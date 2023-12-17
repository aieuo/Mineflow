<?php
declare(strict_types=1);


namespace aieuo\mineflow\formAPI\utils;

use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\element\mineflow\CommandButton;
use aieuo\mineflow\formAPI\element\mineflow\CommandConsoleButton;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\variable\EvaluableString;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\parser\ListExpandingVariableEvaluator;
use aieuo\mineflow\variable\registry\VariableRegistry;
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
            if (count($texts) !== count($extraData)) $extraData = null;

            foreach ($texts as $i => $text) {
                if ($button instanceof CommandConsoleButton) {
                    $buttons[] = new CommandConsoleButton($extraData === null ? $button->getCommand() : $extraData[$i], $text);
                } elseif ($button instanceof CommandButton) {
                    $buttons[] = new CommandButton($extraData === null ? $button->getCommand() : $extraData[$i], $text);
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
}
