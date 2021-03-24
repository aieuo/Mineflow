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
use aieuo\mineflow\trigger\Trigger;
use aieuo\mineflow\trigger\Triggers;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\MapVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\StringVariable;

class FormTrigger extends Trigger {

    /**
     * @param string $key
     * @param string $subKey
     * @return self
     */
    public static function create(string $key, string $subKey = ""): Trigger {
        return new FormTrigger($key, $subKey);
    }

    public function __construct(string $key, string $subKey = "") {
        parent::__construct(Triggers::FORM, $key, $subKey);
    }

    /**
     * @param Form $form
     * @param array|int|bool $data
     * @return array
     * @noinspection PhpMissingParamTypeInspection
     */
    public function getVariables($form, $data = []): array {
        switch ($form) {
            case $form instanceof ModalForm:
                /** @var bool $data */
                $variable = new MapVariable([
                    "data" => new StringVariable($data ? "true" : "false"),
                    "button1" => new MapVariable([
                        "selected" => new StringVariable($data ? "true" : "false"),
                        "text" => new StringVariable($form->getButton1Text()),
                    ],  $form->getButton1Text()),
                    "button2" => new MapVariable([
                        "selected" => new StringVariable($data ? "false" : "true"),
                        "text" => new StringVariable($form->getButton2Text()),
                    ], $form->getButton2Text()),
                ], "form");
                break;
            case $form instanceof ListForm:
                /** @var int $data */
                $variable = new MapVariable([
                    "data" => new NumberVariable($data),
                    "button" => new StringVariable($form->getButton($data)->getText()),
                ], "form");
                break;
            case $form instanceof CustomForm:
                /** @var array $data */
                $dataVariables = [];
                $dropdownVariables = [];
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
                    }
                }
                $variable = new MapVariable([
                    "data" => new ListVariable($dataVariables),
                    "selected" => new ListVariable($dropdownVariables),
                ], "form");
                break;
            default:
                return [];
        }
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
                $form = Main::getFormManager()->getForm($this->getKey());
                if ($form instanceof ListForm) {
                    $button = $form->getButtonById($this->getSubKey());
                    $content = Language::get("trigger.form.string.button", [$this->getKey(), $button instanceof Button ? $button->getText() : ""]);
                } else {
                    $content = $this->getKey();
                }
                break;
        }
        return $content;
    }
}