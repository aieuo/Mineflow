<?php


namespace aieuo\mineflow\utils;

use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\Element;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\ModalForm;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\MapVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
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

    public function getFormDataVariable(Form $form, $data): array {
        switch ($form) {
            case $form instanceof ModalForm:
                $variable = new MapVariable([
                    "data" => new NumberVariable($data ? 0 : 1),
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
                        $dropdown ++;
                    }
                }
                $variable = new MapVariable([
                    "data" => new ListVariable($dataVariables),
                ], "form");
                if (!empty($dropdownVariables)) {
                    $variable->addValue(new ListVariable($dropdownVariables, "selected"));
                }
                break;
            default:
                return [];
        }
        return ["form" => $variable];
    }
}