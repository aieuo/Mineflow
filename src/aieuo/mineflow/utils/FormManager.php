<?php


namespace aieuo\mineflow\utils;

use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\trigger\form\FormTrigger;
use aieuo\mineflow\trigger\TriggerHolder;
use pocketmine\utils\Config;

class FormManager {

    /** @var Config */
    private $config;

    public function __construct(Config $forms) {
        $this->config = $forms;
        $this->config->setJsonOptions(JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING);
    }

    public function saveAll(): void {
        $this->config->save();
    }

    public function existsForm(string $name): bool {
        return $this->config->exists($name);
    }

    public function addForm(string $name, Form $form): void {
        $data = [
            "name" => $name,
            "type" => $form->getType(),
            "form" => $form,
        ];
        $this->config->set($name, $data);
        $this->config->save();
    }

    public function getForm(string $name): ?Form {
        $data = $this->config->get($name);
        if ($data["form"] instanceof Form) return $data["form"];
        if ($data === false) return null;
        return Form::createFromArray($data["form"], $data["name"]);
    }

    public function getAllFormData(): array {
        return $this->config->getAll();
    }

    public function removeForm(string $name): void {
        $this->config->remove($name);
    }

    public function getNotDuplicatedName(string $name): string {
        if (!$this->existsForm($name)) return $name;
        $count = 2;
        while ($this->existsForm($name." (".$count.")")) {
            $count++;
        }
        return $name." (".$count.")";
    }

    public function getAssignedRecipes(string $formName): array {
        $recipes = [];
        $containers = TriggerHolder::getInstance()->getRecipesWithSubKey(FormTrigger::create($formName));
        foreach ($containers as $name => $container) {
            foreach ($container->getAllRecipe() as $recipe) {
                $path = $recipe->getGroup()."/".$recipe->getName();
                if (!isset($recipes[$path])) $recipes[$path] = [];
                $recipes[$path][] = $name;
            }
        }
        return $recipes;
    }
}