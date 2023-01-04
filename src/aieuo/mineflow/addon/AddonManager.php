<?php
declare(strict_types=1);


namespace aieuo\mineflow\addon;

use aieuo\mineflow\exception\MineflowException;
use aieuo\mineflow\flowItem\custom\CustomAction;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\FlowItemFactory;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\Main;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\recipe\RecipePack;
use aieuo\mineflow\trigger\event\EventTrigger;
use aieuo\mineflow\utils\ConfigHolder;
use aieuo\mineflow\utils\dependency\DependencySolver;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Logger;
use aieuo\mineflow\utils\Utils;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\MapVariable;
use SOFe\AwaitGenerator\Await;
use function basename;
use function file_exists;
use function implode;
use function ltrim;
use function mkdir;
use function str_replace;
use function str_starts_with;
use function strtolower;
use function version_compare;

class AddonManager {

    /** @var Addon[] */
    protected array $addons = [];

    /** @var Form[] */
    private array $forms = [];

    public function __construct(
        private string        $directory,
    ) {
        if (!file_exists($directory)) @mkdir($directory, 0777, true);
    }

    public function getDirectory(): string {
        return $this->directory;
    }

    public function loadAddons(): void {
        Await::f2c(function () {
            try {
                yield from $this->loadAddonsGenerator();
            } catch (MineflowException|\Exception $e) {
                Logger::warning($e->getMessage());
            }
        });
    }

    private function loadAddonsGenerator(): \Generator {
        $addons = [];
        $dependency = new DependencySolver();
        $files = Utils::getRecipeFiles($this->getDirectory());
        /** @var \SplFileInfo $file */
        foreach ($files as $file) {
            /** @var Addon $addon */
            $addon = yield from $this->preloadAddon($file->getPathname());
            $addons[$addon->getName()] = $addon;
        }

        foreach ($addons as $name => $addon) {
            $dependencies = $addon->getDependencies();
            foreach ($dependencies as $dep) {
                if (!isset($addons[$dep])) {
                    Logger::warning(Language::get("dependency.addon.addon.not.found", [$name, $dep]));
                    unset($addons[$name]);
                    continue 2;
                }

                if ($name === $dep) {
                    Logger::warning(Language::get("dependency.circular.dependency", [$name." <=> ".$name]));
                    unset($addons[$name]);
                    continue 2;
                }
            }
            $dependency->add($addon->getName(), $dependencies);
        }

        $count = 0;
        $order = $dependency->solve();
        if ($order->hasCircularDependency()) {
            $path = $order->getCircularPath();
            Logger::warning(Language::get("dependency.circular.dependency", [implode(" => ", $path)]));
            return 0;
        }
        foreach ($order->getOrder() as $name) {
            $this->loadAddon($addons[$name]);
            $count ++;
        }
        return $count;
    }

    public function reloadAddons(): \Generator {
        $this->forms = [];

        foreach ($this->addons as $addon) {
            $this->unloadAddon($addon);
        }

        return yield from $this->loadAddonsGenerator();
    }

    public function preloadAddon(string $path): \Generator {
        $pack = RecipePack::load($path, recipeClass: AddonRecipe::class);

        if (version_compare(Main::getInstance()->getDescription()->getVersion(), $pack->getVersion()) < 0) {
            throw new \UnexpectedValueException(Language::get("addon.load.failed", [basename($path), ["import.plugin.outdated"]]));
        }

        $manifest = yield from $this->loadAddonManifest($pack, basename($path));

        return new Addon(
            $pack->getName(),
            $pack->getAuthor(),
            $manifest?->getVariable()?->getValueFromIndex("version")?->getValue() ?? "0.0.0",
            [],
            $pack,
            $manifest,
            $path,
        );
    }

    public function loadAddonManifest(RecipePack $pack, string $filename): \Generator {
        if (($manifestRecipe = $pack->getRecipe("_manifest")) === null) {
            return null;
        }

        /** @var FlowItemExecutor $executor */
        $executor = yield from Await::promise(function ($resolve) use ($manifestRecipe) {
            $manifestRecipe->execute(null, callback: $resolve);
        });

        $variables = $executor->getVariables();
        if (!isset($variables["manifest"])) {
            throw new \UnexpectedValueException(Language::get("addon.load.failed", [$filename, Language::get("addon.manifest.variable.notfound")]));
        }
        $manifestVariable = $variables["manifest"];

        if (!($manifestVariable instanceof MapVariable)) {
            throw new \UnexpectedValueException(Language::get("addon.load.failed", [$filename, Language::get("addon.manifest.variable.type.error")]));
        }

        $recipeInfoVariable = $manifestVariable->getValueFromIndex("recipes");

        if (!($recipeInfoVariable instanceof ListVariable)) {
            throw new \UnexpectedValueException(Language::get("addon.load.failed", [$filename, Language::get("addon.manifest.variable.key.missing", ["recipes", "list"])]));
        }

        $recipeInfos = [];
        foreach ($recipeInfoVariable->getValue() as $i => $recipeInfo) {
            if (!$recipeInfo instanceof MapVariable) {
                throw new \UnexpectedValueException(Language::get("addon.load.failed", [$filename, Language::get("addon.manifest.info.type.error", [$i])]));
            }

            foreach (["id", "category", "path"] as $key) {
                if ($recipeInfo->getValueFromIndex($key) === null) {
                    throw new \UnexpectedValueException(Language::get("addon.load.failed", [$filename, Language::get("addon.manifest.info.key.missing", [$i, $key])]));
                }
            }

            $path = (string)$recipeInfo->getValueFromIndex("path");
            if (!str_starts_with($path, $manifestRecipe->getGroup())) {
                $path = ltrim($manifestRecipe->getGroup()."/".$path, "/");
            }

            $recipeInfos[] = new RecipeInfoAttribute(
                "addon.".strtolower(str_replace(" ", "_", $pack->getName())).".".$recipeInfo->getValueFromIndex("id"),
                (string)$recipeInfo->getValueFromIndex("category"),
                $path,
            );
        }
        return new AddonManifest($recipeInfos, $manifestVariable);
    }

    public function loadAddon(Addon $addon): void {
        $pack = $addon->getPack();
        $manifest = $addon->getManifest();

        if (version_compare(Main::getInstance()->getDescription()->getVersion(), $pack->getVersion()) < 0) {
            throw new \UnexpectedValueException(Language::get("addon.load.failed", [basename($addon->getPath()), ["import.plugin.outdated"]]));
        }

        /** @var \SplObjectStorage|Recipe[] $recipes */
        $recipes = new \SplObjectStorage();
        foreach ($pack->getRecipes() as $recipe) {
            $recipes->attach($recipe);
        }

        if ($manifest instanceof AddonManifest) {
            $recipeManager = Mineflow::getRecipeManager();
            foreach ($manifest->getRecipeInfos() as $recipeInfo) {
                $recipePath = $recipeInfo->getRecipePath();
                $id = $recipeInfo->getActionId();
                $category = $recipeInfo->getCategory();

                [$name, $group] = $recipeManager->parseName($recipePath);
                $recipe = $pack->getRecipe($name, $group);
                if ($recipe === null) {
                    throw new \UnexpectedValueException(Language::get("addon.load.failed", [basename($addon->getPath()), Language::get("addon.manifest.recipe.notfound", [$recipePath])]));
                }

                $recipes->detach($recipe);

                if (($item = FlowItemFactory::get($id)) !== null) {
                    if ($item instanceof CustomAction and $item->getAddonName() === $addon->getName()) {
                        continue;
                    }

                    throw new \UnexpectedValueException(Language::get("addon.load.failed", [basename($addon->getPath()), Language::get("addon.manifest.id.exists", [$id])]));
                }

                $action = new CustomAction($addon->getName(), $id, $category, clone $recipe);
                FlowItemFactory::register($action);
            }
        }

        $loadedRecipes = [];
        $eventManager = Mineflow::getEventManager();
        foreach ($recipes as $recipe) {
            if ($recipe->getName()[0] === "_") continue;

            $loadedRecipes[] = $recipe;

            foreach ($recipe->getTriggers() as $trigger) {
                if ($trigger instanceof EventTrigger and !$eventManager->isTriggerEnabled($trigger)) {
                    $eventManager->setTriggerEnabled($trigger);
                }
            }
        }

        $commandManager = Mineflow::getCommandManager();
        foreach ($pack->getCommands() as $data) {
            $command = $data["command"];
            if (!$commandManager->existsCommand($command) and !$commandManager->isRegistered($command)) {
                $commandManager->registerCommand($command, $data["permission"], $data["description"]);
            }
        }

        foreach ($pack->getForms() as $name => $formData) {
            $form = Form::createFromArray($formData, $name);
            if ($form !== null) {
                $this->addForm($name, $form);
            }
        }

        foreach ($pack->getConfigs() as $name => $data) {
            if (ConfigHolder::existsConfigFile($name)) {
                $config = ConfigHolder::getConfig($name);
                $config->setDefaults($data);
                $config->save();
            } else {
                ConfigHolder::setConfig($name, $data, true);
            }
        }

        $addon->setLoadedRecipes($loadedRecipes);
        $this->addAddon($addon);
    }

    public function unloadAddon(Addon $addon): void {
        foreach ($addon->getLoadedRecipes() as $recipe) {
            $recipe->removeTriggerAll();
        }

        unset($this->addons[spl_object_id($addon)]);
    }

    public function existsFile(string $filename): bool {
        $filename = Utils::getValidFileName($filename);
        $path = $this->getDirectory().$filename.".json";
        return file_exists($path);
    }

    public function getAddons(): array {
        return $this->addons;
    }

    public function addAddon(Addon $addon): void {
        $this->addons[spl_object_id($addon)] = $addon;
    }

    public function getAddonByName(string $name): ?Addon {
        foreach ($this->addons as $addon) {
            if ($addon->getName() === $name) return $addon;
        }
        return null;
    }

    public function getAddonByFilename(string $name): ?Addon {
        foreach ($this->addons as $addon) {
            $filename = basename($addon->getPath(), ".json");
            if ($filename === $name) return $addon;
        }
        return null;
    }

    public function setForms(array $forms): void {
        $this->forms = $forms;
    }

    public function getForms(): array {
        return $this->forms;
    }

    public function addForm(string $name, Form $form): void {
        $this->forms[$name] = $form;
    }

    public function getForm(string $name): ?Form {
        return $this->forms[$name] ?? null;
    }
}
