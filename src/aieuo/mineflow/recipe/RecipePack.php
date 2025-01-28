<?php

namespace aieuo\mineflow\recipe;

use aieuo\mineflow\flowItem\action\command\Command;
use aieuo\mineflow\flowItem\action\command\CommandConsole;
use aieuo\mineflow\flowItem\action\config\CreateConfigVariable;
use aieuo\mineflow\flowItem\action\form\SendForm;
use aieuo\mineflow\formAPI\element\mineflow\button\CommandButton;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\trigger\command\CommandTrigger;
use aieuo\mineflow\trigger\form\FormTrigger;
use aieuo\mineflow\utils\ConfigHolder;
use aieuo\mineflow\utils\Language;
use pocketmine\utils\Filesystem;
use function assert;
use function is_a;
use function json_last_error_msg;

class RecipePack implements \JsonSerializable {

    private string $name;
    private string $author;
    private string $detail;
    /* @var Recipe[] */
    private array $recipes;
    private array $configs;

    private array $commands;
    private array $forms;

    private string $version;

    public function __construct(string $name, string $author, string $detail, array $recipes, ?array $commands = null, ?array $forms = null, ?array $configs = null, string $version = null) {
        $this->name = $name;
        $this->author = $author;
        $this->detail = $detail;
        $this->recipes = $recipes;

        $this->commands = $commands ?? $this->getLinkedCommands();
        $this->forms = $forms ?? $this->getLinkedForms();
        $this->configs = $configs ?? $this->getLinkedConfigFiles();

        $this->version = $version ?? Mineflow::getPluginVersion();
    }

    public function getName(): string {
        return $this->name;
    }

    public function getAuthor(): string {
        return $this->author;
    }

    public function getDetail(): string {
        return $this->detail;
    }

    public function getRecipes(): array {
        return $this->recipes;
    }

    public function getCommands(): array {
        return $this->commands;
    }

    public function getForms(): array {
        return $this->forms;
    }

    public function getConfigs(): array {
        return $this->configs;
    }

    public function getVersion(): string {
        return $this->version;
    }

    private function getLinkedCommands(): array {
        $commandManager = Mineflow::getCommandManager();
        $formManager = Mineflow::getFormManager();
        $commands = [];
        foreach ($this->recipes as $recipe) {
            foreach ($recipe->getTriggers() as $trigger) {
                if ($trigger instanceof CommandTrigger) {
                    $command = $trigger->getCommand();
                    if (!$commandManager->existsCommand($command)) continue;

                    $commands[$command] = $commandManager->getCommand($command);
                }
                if ($trigger instanceof FormTrigger) {
                    $form = $formManager->getForm($trigger->getFormName());
                    if (!$form instanceof ListForm) continue;

                    foreach ($form->getButtons() as $button) {
                        if (!$button instanceof CommandButton) continue;

                        $command = $button->getCommand();
                        if (!$commandManager->existsCommand($command)) continue;

                        $commands[$command] = $commandManager->getCommand($command);
                    }
                }
            }

            foreach ($recipe->getItemsFlatten() as $item) {
                $command = match (true) {
                    $item instanceof Command => $item->getCommand()->getRawString(),
                    $item instanceof CommandConsole => $item->getCommand()->getRawString(),
                    default => null,
                };
                if ($command === null) continue;

                $command = $commandManager->getCommandLabel($command);
                if (!$commandManager->existsCommand($command)) continue;

                $commands[$command] = $commandManager->getCommand($command);
            }
        }
        return $commands;
    }

    private function getLinkedForms(): array {
        $formManager = Mineflow::getFormManager();
        $variableHelper = Mineflow::getVariableHelper();
        $forms = [];
        foreach ($this->recipes as $recipe) {
            foreach ($recipe->getTriggers() as $trigger) {
                if (!($trigger instanceof FormTrigger)) continue;

                $key = $trigger->getFormName();
                $forms[$key] = $formManager->getForm($key);
            }

            foreach ($recipe->getItemsFlatten() as $item) {
                if ($item instanceof SendForm and !$variableHelper->isVariableString($item->getFormName()->getRawString())) {
                    $name = $item->getFormName()->getRawString();
                    $forms[$name] = $formManager->getForm($name);
                }
            }
        }
        return $forms;
    }

    private function getLinkedConfigFiles(): array {
        $configData = [];
        foreach ($this->recipes as $recipe) {
            foreach ($recipe->getItemsFlatten() as $action) {
                if ($action instanceof CreateConfigVariable) {
                    $name = $action->getFileName()->getRawString();
                    $configData[$name] = ConfigHolder::getConfig($name)->getAll();
                }
            }
            foreach ($recipe->getItemsFlatten() as $action) {
                if ($action instanceof CreateConfigVariable) {
                    $name = $action->getFileName()->getRawString();
                    $configData[$name] = ConfigHolder::getConfig($name)->getAll();
                }
            }
        }
        return $configData;
    }

    public function hasRecipe(string $name, string $group = null): bool {
        return $this->getRecipe($name, $group) !== null;
    }

    public function getRecipe(string $name, string $group = null): ?Recipe {
        foreach ($this->recipes as $recipe) {
            if ($recipe->getName() === $name and ($group === null or $recipe->getGroup() === $group)) return $recipe;
        }
        return null;
    }

    public function export(string $path): void {
        if (!file_exists($path)) @mkdir($path, 0777, true);
        FileSystem::safeFilePutContents($path.$this->name.".json", json_encode($this, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING));
    }

    public function jsonSerialize(): array {
        return [
            "name" => $this->name,
            "author" => $this->author,
            "detail" => $this->detail,
            "plugin_version" => $this->version,
            "recipes" => $this->recipes,
            "commands" => $this->commands,
            "forms" => $this->forms,
            "configs" => $this->configs,
        ];
    }

    /**
     * @param string $path
     * @param string $baseGroup
     * @param class-string $recipeClass
     * @return RecipePack
     * @throws \ErrorException
     */
    public static function load(string $path, string $baseGroup = "", string $recipeClass = Recipe::class): RecipePack {
        assert(is_a($recipeClass, Recipe::class, true));

        if (!file_exists($path)) {
            throw new \InvalidArgumentException("Recipe pack ".$path." is not exists.");
        }

        $packData = json_decode(file_get_contents($path), true);
        if ($packData === null) {
            throw new \InvalidArgumentException(Language::get("recipe.json.decode.failed", [$path, json_last_error_msg()]));
        }

        $name = $packData["name"];
        $author = $packData["author"];
        $detail = $packData["detail"];

        $upgrader = new RecipeUpgrader();
        $recipes = [];
        foreach ($packData["recipes"] as $data) {
            $data = $upgrader->upgradeBeforeLoad($data);
            /** @var Recipe $recipe */
            $recipe = new $recipeClass($data["name"], ltrim($baseGroup."/".$data["group"], "/"), $data["author"], $data["plugin_version"] ?? "0");
            $recipe->loadSaveData($data);
            $upgrader->upgradeAfterLoad($recipe);

            $recipes[] = $recipe;
        }

        $commands = $packData["commands"] ?? [];
        $forms = $packData["forms"] ?? [];
        $configs = $packData["configs"] ?? [];

        $version = $packData["plugin_version"];

        return new RecipePack($name, $author, $detail, $recipes, $commands, $forms, $configs, $version);
    }
}