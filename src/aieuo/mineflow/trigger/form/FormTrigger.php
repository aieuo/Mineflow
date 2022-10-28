<?php

namespace aieuo\mineflow\trigger\form;

use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\Element;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\ModalForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\trigger\Trigger;
use aieuo\mineflow\trigger\Triggers;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\BoolVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\MapVariable;
use aieuo\mineflow\variable\NullVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\object\RecipeObjectVariable;
use aieuo\mineflow\variable\StringVariable;

class FormTrigger extends Trigger {

    public static function create(string $key, string $subKey = ""): FormTrigger {
        return new FormTrigger($key, $subKey);
    }

    public function __construct(string $key, string $subKey = "") {
        parent::__construct(Triggers::FORM, $key, $subKey);
    }

    /**
     * @param Form $form
     * @param array|int|bool $data
     * @param Recipe|null $from
     * @return array
     */
    public function getVariables(mixed $form, array|int|bool $data = [], Recipe $from = null): array {
        $variable = new MapVariable([
            "name" => new StringVariable($form->getName()),
            "title" => new StringVariable($form->getTitle()),
        ]);
        switch ($form) {
            case $form instanceof ModalForm:
                /** @var bool $data */
                $variable->setValueAt("data", new BoolVariable($data));
                $variable->setValueAt("button1", new MapVariable([
                    "selected" => new BoolVariable($data),
                    "text" => new StringVariable($form->getButton1Text()),
                ],  $form->getButton1Text()));
                $variable->setValueAt("button2", new MapVariable([
                    "selected" => new BoolVariable(!$data),
                    "text" => new StringVariable($form->getButton2Text()),
                ], $form->getButton2Text()));
                break;
            case $form instanceof ListForm:
                /** @var int $data */
                $variable->setValueAt("data", new NumberVariable($data));
                $variable->setValueAt("button", new StringVariable($form->getButton($data)?->getText() ?? ""));
                break;
            case $form instanceof CustomForm:
                /** @var array $data */
                $dataVariables = [];
                $dropdownVariables = [];
                foreach ($form->getContents() as $i => $content) {
                    $var = match ($content->getType()) {
                        Element::ELEMENT_INPUT => new StringVariable($data[$i]),
                        Element::ELEMENT_TOGGLE => new BoolVariable($data[$i]),
                        Element::ELEMENT_SLIDER, Element::ELEMENT_STEP_SLIDER, Element::ELEMENT_DROPDOWN => new NumberVariable($data[$i]),
                        default => new StringVariable(""),
                    };
                    $dataVariables[$i] = $var;
                    if ($content instanceof Dropdown) {
                        $selected = $content->getOptions()[$data[$i]];
                        $dropdownVariables[] = new StringVariable($selected);
                    }
                }
                $variable->setValueAt("data", new ListVariable($dataVariables));
                $variable->setValueAt("selected", new ListVariable($dropdownVariables));
                break;
            default:
                return [];
        }
        $variable->setValueAt("from", $from === null ? new NullVariable() : new RecipeObjectVariable($from));
        return ["form" => $variable];
    }

    public function __toString(): string {
        switch ($this->getSubKey()) {
            case "":
                $content = Language::get("trigger.form.string.submit", [$this->getKey()]);
                break;
            case "close":
                $content = Language::get("trigger.form.string.close", [$this->getKey()]);
                break;
            default:
                $form = Mineflow::getFormManager()->getForm($this->getKey());
                if ($form instanceof ListForm) {
                    $button = $form->getButtonByUUID($this->getSubKey());
                    $content = Language::get("trigger.form.string.button", [$this->getKey(), $button instanceof Button ? $button->getText() : ""]);
                } else {
                    $content = $this->getKey();
                }
                break;
        }
        return $content;
    }
}
