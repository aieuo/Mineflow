<?php


namespace aieuo\mineflow\utils;

use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\recipe\Recipe;
use pocketmine\utils\Config;

class FormManager {

    /** @var Config */
    private $config;

    public function __construct(Config $forms) {
        $this->config = $forms;
    }

    public function saveAll() {
        $this->config->save();
    }

    public function existsForm(string $name): bool {
        return $this->config->exists($name);
    }

    public function addForm(string $name, Form $form) {
        $data = [
            "name" => $name,
            "type" => $form->getType(),
            "form" => $form,
            "recipes" => $form->getRecipes(),
        ];
        $this->config->set($name, $data);
        $this->config->save();
    }

    public function getForm(string $name): ?Form {
        $data = $this->config->get($name, null);
        return Form::createFromArray($data["form"], $data["name"]);
    }

    public function getAllFormData(): array {
        return $this->config->getAll();
    }

    public function removeForm(string $name) {
        $this->config->remove($name);
    }

    public function addRecipe(string $name, Recipe $recipe) {
        if (!$this->existsForm($name)) return;

        $data = $this->getForm($name);
        $data["recipes"][$recipe->getName()] = true;
        $this->config->set($name, $data);
        $this->config->save();
    }

    public function removeRecipe(string $name, Recipe $recipe): ?int {
        if (!$this->existsForm($name)) return null;

        $data = $this->getForm($name);
        unset($data["recipes"][$recipe->getName()]);
        $this->config->set($name, $data);
        $this->config->save();
        return count($data["recipes"]);
    }

    public function getNotDuplicatedName(string $name): string {
        if (!$this->existsForm($name)) return $name;
        $count = 2;
        while ($this->existsForm($name." (".$count.")")) {
            $count ++;
        }
        $name = $name." (".$count.")";
        return $name;
    }
}