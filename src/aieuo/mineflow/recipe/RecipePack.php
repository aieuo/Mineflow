<?php

namespace aieuo\mineflow\recipe;

use aieuo\mineflow\Main;
use aieuo\mineflow\trigger\Trigger;

class RecipePack implements \JsonSerializable {

    /* @var string */
    private $name;
    /* @var string */
    private $author;
    /* @var string */
    private $detail;
    /* @var Recipe[] */
    private $recipes;

    public function __construct(string $name, string $author, string $detail, array $recipes) {
        $this->name = $name;
        $this->author = $author;
        $this->detail = $detail;
        $this->recipes = $recipes;
    }

    public function getLinkedCommands(): array {
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

    public function getLinkedForms(): array {
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
        file_put_contents($path.$this->name.".json", json_encode($this));
    }

    public function jsonSerialize() {
        return [
            "name" => $this->name,
            "author" => $this->author,
            "detail" => $this->detail,
            "plugin_version" => Main::getInstance()->getDescription()->getVersion(),
            "recipes" => $this->recipes,
            "commands" => $this->getLinkedCommands(),
            "forms" => $this->getLinkedForms(),
        ];
    }
}