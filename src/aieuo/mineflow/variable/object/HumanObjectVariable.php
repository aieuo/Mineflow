<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\entity\Human;

class HumanObjectVariable extends EntityObjectVariable {

    public function __construct(Human $value, ?string $str = null) {
        parent::__construct($value, $str ?? $value->getName());
    }

    public function getValueFromIndex(string $index): ?Variable {
        $variable = parent::getValueFromIndex($index);
        if ($variable !== null) return $variable;

        $human = $this->getHuman();
        switch ($index) {
            case "hand":
                $variable = new ItemObjectVariable($human->getInventory()->getItemInHand());
                break;
            case "food":
                $variable = new NumberVariable($human->getFood());
                break;
            case "xp":
                $variable = new NumberVariable($human->getCurrentTotalXp());
                break;
            case "xp_level":
                $variable = new NumberVariable($human->getXpLevel());
                break;
            case "xp_progress":
                $variable = new NumberVariable($human->getXpProgress());
                break;
            default:
                return null;
        }
        return $variable;
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    public function getHuman(): Human {
        return $this->getValue();
    }

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "hand" => new DummyVariable(DummyVariable::ITEM),
            "food" => new DummyVariable(DummyVariable::NUMBER),
        ]);
    }
}