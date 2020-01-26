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
        $data = $this->config->get($name);
        if ($data["form"] instanceof Form) return $data["form"];
        if ($data === false) return null;;
        return Form::createFromArray($data["form"], $data["name"]);
    }

    public function getAllFormData(): array {
        return $this->config->getAll();
    }

    public function removeForm(string $name) {
        $this->config->remove($name);
    }

    public function addRecipe(string $name, Recipe $recipe, string $button = "") {
        if (!$this->existsForm($name)) return;

        $form = $this->getForm($name);
        $form->addRecipe($recipe->getName(), $button);
        $this->addForm($name, $form);
    }

    public function removeRecipe(string $name, Recipe $recipe, string $button = ""): ?int {
        if (!$this->existsForm($name)) return null;

        $form = $this->getForm($name);
        $form->removeRecipe($recipe->getName(), $button);
        $this->addForm($name, $form);
        return count($form["recipes"]);
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