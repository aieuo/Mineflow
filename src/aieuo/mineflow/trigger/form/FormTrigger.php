<?php
declare(strict_types=1);

namespace aieuo\mineflow\trigger\form;

use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\ModalForm;
use aieuo\mineflow\formAPI\utils\FormUtils;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\trigger\Trigger;
use aieuo\mineflow\trigger\Triggers;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\NullVariable;
use aieuo\mineflow\variable\object\RecipeVariable;

class FormTrigger extends Trigger {

    public function __construct(private readonly string $formName, private string $extraData = "") {
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
        if (!($form instanceof Form)) return [];

        $variable = FormUtils::createFormResponseVariable($form, $data);
        $variable->setValueAt("from", $from === null ? new NullVariable() : new RecipeVariable($from));
        return ["form" => $variable];
    }

    public function hash(): string|int {
        return $this->getFormName().";".$this->getExtraData();
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