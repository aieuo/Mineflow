<?php


namespace aieuo\mineflow\utils;

use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\Element;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\ModalForm;
use aieuo\mineflow\trigger\Trigger;
use aieuo\mineflow\trigger\TriggerHolder;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\MapVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\StringVariable;
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

    public function getAssignedRecipes(string $name): array {
        $recipes = [];
        $containers = TriggerHolder::getInstance()->getRecipesWithSubKey(new Trigger(Trigger::TYPE_FORM, $name));
        foreach ($containers as $name => $container) {
            foreach ($container->getAllRecipe() as $recipe) {
                $path = $recipe->getGroup()."/".$recipe->getName();
                if (!isset($recipes[$path])) $recipes[$path] = [];
                $recipes[$path][] = $name;
            }
        }
        return $recipes;
    }

    public function getFormDataVariable(Form $form, $data): array {
        switch ($form) {
            case $form instanceof ModalForm:
                $variable = new MapVariable([
                    "data" => new StringVariable($data ? "true" : "false"),
                    "button1" => new MapVariable([
                        "selected" => new StringVariable($data ? "true" : "false"),
                        "text" => new StringVariable($form->getButton1()),
                    ], "button1", $form->getButton1()),
                    "button2" => new MapVariable([
                        "selected" => new StringVariable($data ? "false" : "true"),
                        "text" => new StringVariable($form->getButton2()),
                    ], "button2", $form->getButton2()),
                ], "form");
                break;
            case $form instanceof ListForm:
                $variable = new MapVariable([
                    "data" => new NumberVariable($data),
                    "button" => new StringVariable($form->getButton($data)->getText()),
                ], "form");
                break;
            case $form instanceof CustomForm:
                $dataVariables = [];
                $dropdownVariables = [];
                $dropdown = 0;
                foreach ($form->getContents() as $i => $content) {
                    switch ($content->getType()) {
                        case Element::ELEMENT_INPUT:
                            $var = new StringVariable($data[$i]);
                            break;
                        case Element::ELEMENT_TOGGLE:
                            $var = new StringVariable($data[$i] ? "true" : "false");
                            break;
                        case Element::ELEMENT_SLIDER:
                        case Element::ELEMENT_STEP_SLIDER:
                        case Element::ELEMENT_DROPDOWN:
                            $var = new NumberVariable($data[$i]);
                            break;
                        default:
                            $var = new StringVariable("");
                            break;
                    }
                    $dataVariables[$i] = $var;
                    if ($content instanceof Dropdown) {
                        $selected = $content->getOptions()[$data[$i]];
                        $dropdownVariables[] = new StringVariable($selected);
                        $dropdown++;
                    }
                }
                $variable = new MapVariable([
                    "data" => new ListVariable($dataVariables, "data"),
                    "selected" => new ListVariable($dropdownVariables, "selected"),
                ], "form");
                break;
            default:
                return [];
        }
        return ["form" => $variable];
    }
}