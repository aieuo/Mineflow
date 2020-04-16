<?php

namespace aieuo\mineflow\recipe;

use aieuo\mineflow\exception\FlowItemLoadException;
use aieuo\mineflow\Main;
use aieuo\mineflow\trigger\Trigger;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Logger;

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
    private $commands;
    /* @var array */
    private $forms;

    /* @var string */
    private $version;

    public function __construct(string $name, string $author, string $detail, array $recipes, array $commands = null, array $forms = null, string $version = null) {
        $this->name = $name;
        $this->author = $author;
        $this->detail = $detail;
        $this->recipes = $recipes;

        $this->commands = $commands ?? $this->getLinkedCommands();
        $this->forms = $forms ?? $this->getLinkedForms();

        $this->version = $version ?? Main::getInstance()->getDescription()->getVersion();
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

    private function getLinkedCommands(): array {
        $commandManager = Main::getCommandManager();
        $commands = [];
        foreach ($this->recipes as $recipe) {
            foreach ($recipe->getTriggers() as $trigger) {
                if ($trigger->getType() !== Trigger::TYPE_COMMAND) continue;

                $commands[$trigger->getKey()] = $commandManager->getCommand($trigger->getKey());
            }
        }
        return $commands;
    }

    private function getLinkedForms(): array {
        $formManager = Main::getFormManager();
        $forms = [];
        foreach ($this->recipes as $recipe) {
            foreach ($recipe->getTriggers() as $trigger) {
                if ($trigger->getType() !== Trigger::TYPE_FORM) continue;

                $forms[$trigger->getKey()] = $formManager->getForm($trigger->getKey());
            }
        }
        return $forms;
    }

    public function export(string $path) {
        if (!file_exists($path)) @mkdir($path, 0777, true);
        file_put_contents($path.$this->name.".json", json_encode($this, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING));
    }

    public function jsonSerialize() {
        return [
            "name" => $this->name,
            "author" => $this->author,
            "detail" => $this->detail,
            "plugin_version" => $this->version,
            "recipes" => $this->recipes,
            "commands" => $this->commands,
            "forms" => $this->forms,
        ];
    }

    /**
     * @param string $path
     * @return RecipePack|null
     * @throws FlowItemLoadException
     */
    public static function import(string $path): ?RecipePack {
        if (!file_exists($path)) return null;

        $packData = json_decode(file_get_contents($path), true);

        $name = $packData["name"];
        $author = $packData["author"];
        $detail = $packData["detail"];

        $recipes = [];
        foreach ($packData["recipes"] as $data) {
            $recipe = new Recipe($data["name"], $data["group"], $data["author"]);
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

        $version = $packData["plugin_version"];

        return new RecipePack($name, $author, $detail, $recipes, $commands, $forms, $version);
    }
}