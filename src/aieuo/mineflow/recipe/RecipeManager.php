<?php

namespace aieuo\mineflow\recipe;

use aieuo\mineflow\event\MineflowRecipeLoadEvent;
use aieuo\mineflow\exception\FlowItemLoadException;
use aieuo\mineflow\flowItem\action\script\ExecuteRecipe;
use aieuo\mineflow\flowItem\custom\CustomAction;
use aieuo\mineflow\flowItem\FlowItemContainer;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\FlowItemFactory;
use aieuo\mineflow\Main;
use aieuo\mineflow\recipe\template\AddonManifestRecipeTemplate;
use aieuo\mineflow\recipe\template\CommandAliasRecipeTemplate;
use aieuo\mineflow\recipe\template\RecipeTemplate;
use aieuo\mineflow\recipe\template\SpecificBlockRecipeTemplate;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Logger;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\MapVariable;
use aieuo\mineflow\variable\StringVariable;
use ErrorException;
use RegexIterator;
use SOFe\AwaitGenerator\Await;
use function file_get_contents;
use function json_decode;
use function json_last_error_msg;
use function ltrim;
use function str_replace;
use function str_starts_with;
use function version_compare;
use const PHP_EOL;

class RecipeManager {

    /** @var Recipe[][] */
    protected array $recipes = [];

    private array $templates = [];

    public function __construct(
        private string $recipeDirectory,
        private string $addonDirectory,
    ) {
        if (!file_exists($recipeDirectory)) @mkdir($recipeDirectory, 0777, true);
        if (!file_exists($addonDirectory)) @mkdir($addonDirectory, 0777, true);
    }

    public function getRecipeDirectory(): string {
        return $this->recipeDirectory;
    }

    public function getAddonDirectory(): string {
        return $this->addonDirectory;
    }

    /**
     * @param string $path
     * @return \Iterator<\SplFileInfo>
     */
    public function getRecipeFiles(string $path): \Iterator {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $path,
                \FilesystemIterator::CURRENT_AS_FILEINFO | \FilesystemIterator::KEY_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS
            )
        );
        return new \RegexIterator($files, '/\.json$/', RegexIterator::MATCH);
    }

    public function loadRecipes(): void {
        $files = $this->getRecipeFiles($this->getRecipeDirectory());
        foreach ($files as $file) {
            /** @var \SplFileInfo $file */
            $pathname = $file->getPathname();
            $group = str_replace(
                ["\\", str_replace("\\", "/", $this->getRecipeDirectory())],
                ["/", ""], $file->getPath());
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

            $recipe = new Recipe($data["name"], $group, $data["author"] ?? "", $data["plugin_version"] ?? "0");
            $recipe->setRawData($json);
            try {
                $recipe->loadSaveData($data);
            } catch (\ErrorException|\UnexpectedValueException $e) {
                Logger::warning(Language::get("recipe.load.failed", [$data["name"], $e->getMessage()]).PHP_EOL);
                continue;
            } catch (FlowItemLoadException $e) {
                Logger::warning(Language::get("recipe.load.failed", [$data["name"], ""]));
                Logger::warning($e->getMessage().PHP_EOL);
                continue;
            }
            $recipe->checkVersion();

            (new MineflowRecipeLoadEvent(Main::getInstance(), $recipe))->call();

            $this->add($recipe, false);
        }
    }

    public function loadAddons(): void {
        Await::f2c(function () {
            $files = $this->getRecipeFiles($this->getAddonDirectory());
            /** @var \SplFileInfo $file */
            foreach ($files as $file) {
                try {
                    $pack = RecipePack::load($file->getPathname());
                } catch (\ErrorException|\UnexpectedValueException|FlowItemLoadException|\InvalidArgumentException $e) {
                    Logger::warning(Language::get("addon.load.failed", [$file->getBasename(), $e->getMessage()]));
                    continue;
                }

                if (version_compare(Main::getInstance()->getDescription()->getVersion(), $pack->getVersion()) < 0) {
                    Logger::warning(Language::get("addon.load.failed", [$file->getBasename(), ["import.plugin.outdated"]]));
                    continue;
                }

                if (($manifestRecipe = $pack->getRecipe("_manifest")) === null) {
                    Logger::warning(Language::get("addon.load.failed", [$file->getBasename(), Language::get("addon.manifest.notfound")]));
                    continue;
                }

                /** @var FlowItemExecutor $executor */
                $executor = yield from Await::promise(function ($resolve) use($manifestRecipe) {
                    $executor = new FlowItemExecutor([], null, onComplete: $resolve);
                    $executor->setWaiting();

                    $manifestRecipe->execute(null, callbackExecutor: $executor);
                });

                $variables = $executor->getVariables();
                if (!isset($variables["manifest"])) {
                    Logger::warning(Language::get("addon.load.failed", [$file->getBasename(), Language::get("addon.manifest.variable.notfound")]));
                    continue;
                }
                $manifest = $variables["manifest"];

                if (!($manifest instanceof MapVariable)) {
                    Logger::warning(Language::get("addon.load.failed", [$file->getBasename(), Language::get("addon.manifest.variable.type.error")]));
                    continue;
                }

                $addonId = $manifest->getValueFromIndex("id");
                $recipeInfos = $manifest->getValueFromIndex("recipes");

                if (!($addonId instanceof StringVariable)) {
                    Logger::warning(Language::get("addon.load.failed", [$file->getBasename(), Language::get("addon.manifest.variable.key.missing", ["id", "string"])]));
                    continue;
                }
                if (!($recipeInfos instanceof ListVariable)) {
                    Logger::warning(Language::get("addon.load.failed", [$file->getBasename(), Language::get("addon.manifest.variable.key.missing", ["recipes", "list"])]));
                    continue;
                }

                $recipeManager = Main::getRecipeManager();
                foreach ($recipeInfos->getValue() as $i => $value) {
                    if (!$value instanceof MapVariable) {
                        Logger::warning(Language::get("addon.load.failed", [$file->getBasename(), Language::get("addon.manifest.info.type.error", [$i])]));
                        continue;
                    }

                    $recipeInfo = $value->getValue();
                    foreach (["id", "category", "path"] as $key) {
                        if (!isset($recipeInfo[$key])) {
                            Logger::warning(Language::get("addon.load.failed", [$file->getBasename(), Language::get("addon.manifest.info.key.missing", [$i, $key])]));
                            continue 2;
                        }
                    }

                    $path = $recipeInfo["path"];
                    $id = "addon.".$addonId.".".$recipeInfo["id"];
                    $category = $recipeInfo["category"];
                    if (!str_starts_with($path, $manifestRecipe->getGroup())) {
                        $path = ltrim($manifestRecipe->getGroup()."/".$path, "/");
                    }

                    [$name, $group] = $recipeManager->parseName($path);
                    $recipe = $pack->getRecipe($name, $group);
                    if ($recipe === null) {
                        Logger::warning(Language::get("addon.load.failed", [$file->getBasename(), Language::get("addon.manifest.recipe.notfound", [$path])]));
                        continue;
                    }

                    if (FlowItemFactory::get($id) !== null) {
                        Logger::warning(Language::get("addon.load.failed", [$file->getBasename(), Language::get("addon.manifest.id.exists", [$id])]));
                        continue;
                    }

                    $action = new CustomAction($id, $category, clone $recipe);
                    FlowItemFactory::register($action);
                }
            }
        });
    }

    public function exists(string $name, string $group = ""): bool {
        return isset($this->recipes[$group][$name]);
    }

    public function add(Recipe $recipe, bool $createFile = true): void {
        $this->recipes[$recipe->getGroup()][$recipe->getName()] = $recipe;
        if ($createFile and !file_exists($recipe->getFileName($this->getRecipeDirectory()))) {
            $recipe->save($this->getRecipeDirectory());
        }
    }

    public function get(string $name, string $group = ""): ?Recipe {
        return $this->recipes[$group][$name] ?? null;
    }

    public function getByPath(string $name): array {
        if (empty($name)) return $this->getAll();

        $result = [];
        foreach ($this->getAll() as $group => $item) {
            if (str_starts_with($group."/", $name."/")) $result[$group] = $item;
        }
        return $result;
    }

    /**
     * @return Recipe[][]
     */
    public function getAll(): array {
        return $this->recipes;
    }

    public function remove(string $name, string $group = ""): void {
        if (!$this->exists($name, $group)) return;
        unlink($this->get($name, $group)->getFileName($this->getRecipeDirectory()));
        unset($this->recipes[$group][$name]);
    }

    public function deleteGroup(string $group): bool {
        try {
            $deleted = rmdir($this->getRecipeDirectory().$group);
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
                $recipe->save($this->getRecipeDirectory());
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
        $oldPath = $recipe->getFileName($this->getRecipeDirectory());
        $recipe->setName($newName);
        unset($this->recipes[$group][$recipeName]);
        $this->recipes[$group][$newName] = $recipe;
        rename($oldPath, $recipe->getFileName($this->getRecipeDirectory()));
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
        $recipeManager = Main::getRecipeManager();

        $recipes = [];
        if ($base) $recipes[] = [$origin->getGroup()."/".$origin->getName() => $origin];
        foreach ($recipe->getActions() as $action) {
            if ($action instanceof FlowItemContainer) {
                $links = $this->getWithLinkedRecipes($action, $origin, false);
                $recipes[] = $links;
                continue;
            }

            if ($action instanceof ExecuteRecipe) {
                $name = Main::getVariableHelper()->replaceVariables($action->getRecipeName(), []);

                [$recipeName, $group] = $recipeManager->parseName($name);
                if (empty($group)) $group = $origin->getGroup();

                $recipe = $recipeManager->get($recipeName, $group);
                if ($recipe === null) $recipe = $recipeManager->get($recipeName, "");
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
