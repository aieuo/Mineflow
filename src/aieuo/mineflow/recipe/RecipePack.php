<?php

namespace aieuo\mineflow\recipe;

use aieuo\mineflow\exception\FlowItemLoadException;
use aieuo\mineflow\flowItem\action\CreateConfigVariable;
use aieuo\mineflow\Main;
use aieuo\mineflow\trigger\Triggers;
use aieuo\mineflow\utils\ConfigHolder;

class RecipePack implements \JsonSerializable {

    /* @var string */
    private $name;
    /* @var string */
    private $author;
    /* @var string */
    private $detail;
    /* @var Recipe[] */
    private $recipes;
    /* @var array */
    private $configs;

    /* @var array */
    private $commands;
    /* @var array */
    private $forms;

    /* @var string */
    private $version;

    public function __construct(string $name, string $author, string $detail, array $recipes, ?array $commands = null, ?array $forms = null, ?array $configs = null, string $version = null) {
        $this->name = $name;
        $this->author = $author;
        $this->detail = $detail;
        $this->recipes = $recipes;

        $this->commands = $commands ?? $this->getLinkedCommands();
        $this->forms = $forms ?? $this->getLinkedForms();
        $this->configs = $configs ?? $this->getLinkedConfigFiles();

        $this->version = $version ?? Main::getPluginVersion();
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
        $commandManager = Main::getCommandManager();
        $commands = [];
        foreach ($this->recipes as $recipe) {
            foreach ($recipe->getTriggers() as $trigger) {
                if ($trigger->getType() !== Triggers::COMMAND) continue;

                $key = $trigger->getKey();
                $commands[$key] = $commandManager->getCommand($key);
            }
        }
        return $commands;
    }

    private function getLinkedForms(): array {
        $formManager = Main::getFormManager();
        $forms = [];
        foreach ($this->recipes as $recipe) {
            foreach ($recipe->getTriggers() as $trigger) {
                if ($trigger->getType() !== Triggers::FORM) continue;

                $key = $trigger->getKey();
                $forms[$key] = $formManager->getForm($key);
            }
        }
        return $forms;
    }

    private function getLinkedConfigFiles(): array {
        $configData = [];
        foreach ($this->recipes as $recipe) {
            foreach ($recipe->getActions() as $action) {
                if ($action instanceof CreateConfigVariable) {
                    $name = $action->getFileName();
                    $configData[$name] = ConfigHolder::getConfig($name)->getAll();
                }
            }
        }
        return $configData;
    }

    public function export(string $path): void {
        if (!file_exists($path)) @mkdir($path, 0777, true);
        file_put_contents($path.$this->name.".json", json_encode($this, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING));
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
     * @return RecipePack|null
     * @throws FlowItemLoadException|\ErrorException
     */
    public static function import(string $path): ?RecipePack {
        if (!file_exists($path)) return null;

        $packData = json_decode(file_get_contents($path), true);

        $name = $packData["name"];
        $author = $packData["author"];
        $detail = $packData["detail"];

        $recipes = [];
        foreach ($packData["recipes"] as $data) {
            $recipe = new Recipe($data["name"], $data["group"], $data["author"], $data["plugin_version"] ?? null);
            $recipe->loadSaveData($data["actions"]);

            $recipe->setTargetSetting(
                $data["target"]["type"] ?? Recipe::TARGET_DEFAULT,
                $data["target"]["options"] ?? []
            );
            $recipe->setTriggersFromArray($data["triggers"] ?? []);
            $recipe->setArguments($data["arguments"] ?? []);
            $recipe->setReturnValues($data["returnValues"] ?? []);

            $recipes[] = $recipe;
        }

        $commands = $packData["commands"] ?? [];
        $forms = $packData["forms"] ?? [];
        $configs = $packData["configs"] ?? [];

        $version = $packData["plugin_version"];

        return new RecipePack($name, $author, $detail, $recipes, $commands, $forms, $configs, $version);
    }
}