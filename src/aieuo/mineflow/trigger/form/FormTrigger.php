<?php

namespace aieuo\mineflow\trigger\form;

use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\Element;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\ModalForm;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\trigger\Trigger;
use aieuo\mineflow\trigger\Triggers;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\BooleanVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\MapVariable;
use aieuo\mineflow\variable\NullVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\object\RecipeVariable;
use aieuo\mineflow\variable\StringVariable;

class FormTrigger extends Trigger {

    public function __construct(private string $formName, private string $extraData = "") {
        parent::__construct(Triggers::FORM);
    }

    public function getFormName(): string {
        return $this->formName;
    }

    public function getExtraData(): string {
        return $this->extraData;
    }

    public function setExtraData(string $extraData): void {
        $this->extraData = $extraData;
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
                $variable->setValueAt("data", new BooleanVariable($data));
                $variable->setValueAt("button1", new MapVariable([
                    "selected" => new BooleanVariable($data),
                    "text" => new StringVariable($form->getButton1Text()),
                ],  $form->getButton1Text()));
                $variable->setValueAt("button2", new MapVariable([
                    "selected" => new BooleanVariable(!$data),
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
                        Element::ELEMENT_TOGGLE => new BooleanVariable($data[$i]),
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
        $variable->setValueAt("from", $from === null ? new NullVariable() : new RecipeVariable($from));
        return ["form" => $variable];
    }

    public function hash(): string|int {
        return $this->getFormName().";".$this->getExtraData();
    }

    public function serialize(): array {
        return [
            "formName" => $this->formName,
            "extraData" => $this->extraData,
        ];
    }

    public static function deserialize(array $data): FormTrigger {
        return new FormTrigger($data["formName"] ?? $data["key"], $data["extraData"] ?? $data["subKey"]);
    }

    public function __toString(): string {
        switch ($this->getExtraData()) {
            case "":
                $content = Language::get("trigger.form.string.submit", [$this->getFormName()]);
                break;
            case "close":
                $content = Language::get("trigger.form.string.close", [$this->getFormName()]);
                break;
            default:
                $form = Mineflow::getFormManager()->getForm($this->getFormName());
                if ($form instanceof ListForm) {
                    $button = $form->getButtonByUUID($this->getExtraData());
                    $content = Language::get("trigger.form.string.button", [$this->getFormName(), $button instanceof Button ? $button->getText() : ""]);
                } elseif ($form instanceof ModalForm) {
                    $button = ($this->getExtraData() === "1" ? "yes" : "no");
                    $content = Language::get("trigger.form.string.button", [$this->getFormName(), Language::get("form.".$button)]);
                } else {
                    $content = $this->getFormName();
                }
                break;
        }
        return $content;
    }
}
