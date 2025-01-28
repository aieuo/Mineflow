<?php
declare(strict_types=1);


namespace aieuo\mineflow\formAPI\utils;

use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\Element;
use aieuo\mineflow\formAPI\element\mineflow\button\CommandButton;
use aieuo\mineflow\formAPI\element\mineflow\button\CommandConsoleButton;
use aieuo\mineflow\formAPI\element\mineflow\button\FormButton;
use aieuo\mineflow\formAPI\element\mineflow\button\RecipeButton;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\ModalForm;
use aieuo\mineflow\variable\BooleanVariable;
use aieuo\mineflow\variable\EvaluableString;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\MapVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\object\CustomFormVariable;
use aieuo\mineflow\variable\object\ListFormVariable;
use aieuo\mineflow\variable\object\ModalFormVariable;
use aieuo\mineflow\variable\parser\ListExpandingVariableEvaluator;
use aieuo\mineflow\variable\registry\VariableRegistry;
use aieuo\mineflow\variable\StringVariable;
use function count;

class FormUtils {

    /**
     * @param ListForm $form
     * @param VariableRegistry $registry
     * @return Button[]
     */
    public static function expandListFormButtons(ListForm $form, VariableRegistry $registry): array {
        $buttons = [];
        foreach ($form->getButtons() as $button) {
            $texts = self::expandText(new EvaluableString($button->getText()), $registry);
            $extraData = [];
            if ($button instanceof CommandButton) {
                $extraData = self::expandText(new EvaluableString($button->getCommand()), $registry);
            }
            if ($button instanceof FormButton) {
                $extraData = self::expandText(new EvaluableString($button->getFormName()), $registry);
            }
            if ($button instanceof RecipeButton) {
                $extraData = self::expandText(new EvaluableString($button->getRecipeName()), $registry);
            }
            if (count($texts) !== count($extraData)) $extraData = null;

            foreach ($texts as $i => $text) {
                if ($button instanceof CommandConsoleButton) {
                    $buttons[] = new CommandConsoleButton($extraData === null ? $button->getCommand() : $extraData[$i], $text);
                } elseif ($button instanceof CommandButton) {
                    $buttons[] = new CommandButton($extraData === null ? $button->getCommand() : $extraData[$i], $text);
                } elseif ($button instanceof FormButton) {
                    $buttons[] = new FormButton($extraData === null ? $button->getFormName() : $extraData[$i], $text);
                } elseif ($button instanceof RecipeButton) {
                    $buttons[] = new RecipeButton($extraData === null ? $button->getRecipeName() : $extraData[$i], $text);
                } else {
                    $buttons[] = new Button($text);
                }
            }
        }

        return $buttons;
    }

    /**
     * @param EvaluableString $string $string
     * @param VariableRegistry $registry
     * @return string[]
     * @throws \Exception
     */
    public static function expandText(EvaluableString $string, VariableRegistry $registry): array {
        $evaluator = new ListExpandingVariableEvaluator($registry, true);

        $texts = [];
        $ast = $string->getAst();
        if ($string->isSimpleText() or $ast === null) {
            $texts[] = $string->eval($registry, true);
        } else {
            $variable = $evaluator->eval($ast);
            if ($variable instanceof ListVariable) {
                foreach ($variable->getIterator() as $value) {
                    $texts[] = (string)$value;
                }
            } else {
                $texts[] = (string)$variable;
            }
        }

        return $texts;
    }

    public static function createFormResponseVariable(Form $form, array|int|bool $data): MapVariable {
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
                $variable->setValueAt("form", new ModalFormVariable($form));
                break;
            case $form instanceof ListForm:
                /** @var int $data */
                $variable->setValueAt("data", new NumberVariable($data));
                $variable->setValueAt("button", new StringVariable($form->getButton($data)?->getText() ?? ""));
                $variable->setValueAt("form", new ListFormVariable($form));
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
                $variable->setValueAt("form", new CustomFormVariable($form));
                break;
        }
        return $variable;
    }
}