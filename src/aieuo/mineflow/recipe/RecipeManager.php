<?php

namespace aieuo\mineflow\recipe;

use aieuo\mineflow\event\MineflowRecipeLoadEvent;
use aieuo\mineflow\exception\FlowItemLoadException;
use aieuo\mineflow\flowItem\action\script\ExecuteRecipeBase;
use aieuo\mineflow\flowItem\FlowItemContainer;
use aieuo\mineflow\Main;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\recipe\template\AddonManifestRecipeTemplate;
use aieuo\mineflow\recipe\template\CommandAliasRecipeTemplate;
use aieuo\mineflow\recipe\template\RecipeTemplate;
use aieuo\mineflow\recipe\template\SpecificBlockRecipeTemplate;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Logger;
use aieuo\mineflow\utils\Utils;
use ErrorException;
use pocketmine\Server;
use Symfony\Component\Filesystem\Path;
use function file_get_contents;
use function json_decode;
use function json_last_error_msg;
use function ltrim;
use function rtrim;
use function str_replace;
use function str_starts_with;
use const PHP_EOL;

class RecipeManager {

    /** @var Recipe[][] */
    protected array $recipes = [];

    private array $templates = [];

    public function __construct(
        private readonly string $directory,
        private readonly ?\Closure $recipeCreationFunc = null,
    ) {
        if (!file_exists($directory)) @mkdir($directory, 0777, true);
    }

    public function getDirectory(): string {
        return $this->directory;
    }

    private function createRecipe(string $name, string $group, string $author, string $version): Recipe {
        if ($this->recipeCreationFunc !== null) {
            $recipe = ($this->recipeCreationFunc)($name, $group, $author, $version);
        } else {
            $recipe = new Recipe($name, $group, $author, $version);
        }
        return $recipe;
    }

    public function loadRecipes(): void {
        $pluginManager = Server::getInstance()->getPluginManager();
        $addonManager = Mineflow::getAddonManager();
        $files = Utils::getRecipeFiles($this->getDirectory());
        foreach ($files as $file) {
            /** @var \SplFileInfo $file */
            $pathname = $file->getPathname();
            $group = str_replace(rtrim(str_replace("\\", "/", $this->getDirectory()), "/"), "", rtrim(str_replace("\\", "/", $file->getPath()), "/"));
            $group = ltrim($group, "/");

            $json = file_get_contents($pathname);
            $data = json_decode($json, true);
            if ($data === null) {
                Logger::warning(Language::get("recipe.json.decode.failed", [$pathname, json_last_error_msg()]));
                continue;
            }

            if (!isset($data["name"]) or !isset($data["actions"])) {
                Logger::warning(Language::get("recipe.json.decode.failed", [$pathname, ["recipe.json.key.missing"]]));
                continue;
            }

            foreach ($data["dependency"]["plugin"] ?? [] as $dependency) {
                if ($pluginManager->getPlugin($dependency) === null) {
                    Logger::warning(Language::get("dependency.plugin.not.found", [$data["name"], $dependency]));
                    continue 2;
                }
            }

            foreach ($data["dependency"]["addon"] ?? [] as $dependency) {
                if ($addonManager->getAddonByName($dependency) === null) {
                    Logger::warning(Language::get("dependency.addon.not.found", [$data["name"], $dependency]));
                    continue 2;
                }
            }


            $upgrader = new RecipeUpgrader();
            try {
                $data = $upgrader->upgradeBeforeLoad($data);
                $recipe = $this->createRecipe($data["name"], $group, $data["author"] ?? "", $data["plugin_version"] ?? "0");
                $recipe->setRawData($json);
                $recipe->loadSaveData($data);
                $upgrader->upgradeAfterLoad($recipe);
            } catch (ErrorException|\UnexpectedValueException $e) {
                Logger::warning(Language::get("recipe.load.failed", [$data["name"], $e->getMessage()]).PHP_EOL);
                if (Mineflow::isDebug()) {
                    Main::getInstance()->getLogger()->logException($e);
                }
                continue;
            } catch (FlowItemLoadException $e) {
                Logger::warning(Language::get("recipe.load.failed", [$data["name"], ""]));
                Logger::warning($e->getMessage().PHP_EOL);
                if (Mineflow::isDebug()) {
                    Main::getInstance()->getLogger()->logException($e);
                }
                continue;
            }

            (new MineflowRecipeLoadEvent(Main::getInstance(), $recipe))->call();

            $this->add($recipe, false);
        }
    }

    public function exists(string $name, string $group = ""): bool {
        return isset($this->recipes[$group][$name]);
    }

    public function add(Recipe $recipe, bool $createFile = true): void {
        $this->recipes[$recipe->getGroup()][$recipe->getName()] = $recipe;
        if ($createFile and !file_exists($recipe->getFileName($this->getDirectory()))) {
            $recipe->save($this->getDirectory());
        }
    }

    public function get(string $name, string $group = ""): ?Recipe {
        return $this->recipes[$group][$name] ?? null;
    }

    public function getByPath(string $name, bool $includeReadonly = false): array {
        $result = [];
        foreach ($this->getAll() as $group => $items) {
            if (!$includeReadonly) {
                foreach ($items as $item) {
                    if ($item->isReadonly()) continue 2;
                }
            }

            if (empty($name) or str_starts_with($group."/", $name."/")) {
                $result[$group] = $items;
            }
        }
        return $result;
    }

    /**
     * @return Recipe[][]
     */
    public function getAll(): array {
        return $this->recipes;
    }

    public function unload(string $name, string $group = ""): void {
        $recipe = $this->get($name, $group);
        if ($recipe === null) return;

        $recipe->removeTriggerAll();
        unset($this->recipes[$group][$name]);
    }

    public function remove(string $name, string $group = ""): void {
        $recipe = $this->get($name, $group);
        if ($recipe === null) return;

        $recipe->unlink($this->getDirectory());
        unset($this->recipes[$group][$name]);
    }

    public function deleteGroup(string $group): bool {
        try {
            $deleted = rmdir(Path::join($this->getDirectory(), $group));
            if ($deleted) {
                unset($this->recipes[$group]);
            }
            return $deleted;
        } catch (ErrorException) {
            return false;
        }
    }

    public function saveAll(): void {
        foreach ($this->getAll() as $group) {
            foreach ($group as $recipe) {
                $recipe->save($this->getDirectory());
            }
        }
    }

    public function getNotDuplicatedName(string $name, string $group = ""): string {
        if (!$this->exists($name, $group)) return $name;
        $count = 2;
        while ($this->exists($name." (".$count.")", $group)) {
            $count++;
        }
        return $name." (".$count.")";
    }

    public function rename(string $recipeName, string $newName, string $group = ""): void {
        if (!$this->exists($recipeName, $group)) return;
        $recipe = $this->get($recipeName, $group);
        $oldPath = $recipe->getFileName($this->getDirectory());
        $recipe->setName($newName);
        unset($this->recipes[$group][$recipeName]);
        $this->recipes[$group][$newName] = $recipe;
        rename($oldPath, $recipe->getFileName($this->getDirectory()));
    }

    public function parseName(string $name): array {
        $names = explode("/", $name);
        return [array_pop($names), implode("/", $names)];
    }

    public function getParentPath(string $group): string {
        $names = explode("/", $group);
        array_pop($names);
        return implode("/", $names);
    }

    public function getWithLinkedRecipes(FlowItemContainer $recipe, Recipe $origin, bool $base = true): array {
        $recipeManager = Mineflow::getRecipeManager();

        $recipes = [];
        if ($base) $recipes[] = [$origin->getGroup()."/".$origin->getName() => $origin];
        foreach ($recipe->getItems() as $action) {
            foreach ($action->getArguments() as $argument) {
                if ($argument instanceof FlowItemContainer) {
                    $links = $this->getWithLinkedRecipes($argument, $origin, false);
                    $recipes[] = $links;
                }
            }

            if ($action instanceof ExecuteRecipeBase) {
                $name = Mineflow::getVariableHelper()->replaceVariables($action->getRecipeName(), []);

                [$recipeName, $group] = $this->parseName($name);
                if (empty($group)) $group = $origin->getGroup();

                $recipe = $this->get($recipeName, $group) ?? $recipeManager->get($recipeName, $group);
                if ($recipe === null) $recipe = $this->get($recipeName, "") ?? $recipeManager->get($recipeName, "");
                if ($recipe === null) continue;

                $recipes[] = $this->getWithLinkedRecipes($recipe, $recipe);
            }
        }
        return array_merge([], ...$recipes);
    }

    public function addTemplates(): void {
        $this->addTemplate(CommandAliasRecipeTemplate::class);
        $this->addTemplate(SpecificBlockRecipeTemplate::class);
        $this->addTemplate(AddonManifestRecipeTemplate::class);
    }

    /**
     * @param class-string<RecipeTemplate> $templateClass
     */
    public function addTemplate(string $templateClass): void {
        $this->templates[] = $templateClass;
    }

    public function getTemplates(): array {
        return $this->templates;
    }
}