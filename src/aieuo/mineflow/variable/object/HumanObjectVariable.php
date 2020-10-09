<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\entity\Human;

class HumanObjectVariable extends EntityObjectVariable {

    public function __construct(Human $value, string $name = "", ?string $str = null) {
        parent::__construct($value, $name, $str ?? $value->getName());
    }

    public function getValueFromIndex(string $index): ?Variable {
        $variable = parent::getValueFromIndex($index);
        if ($variable !== null) return $variable;

        $human = $this->getHuman();
        switch ($index) {
            case "hand":
                $variable = new ItemObjectVariable($human->getInventory()->getItemInHand(), "hand");
                break;
            case "food":
                $variable = new NumberVariable($human->getFood(), "food");
                break;
            default:
                return null;
        }
        return $variable;
    }

    public function getHuman(): Human {
        /** @var Human $value */
        $value = $this->getValue();
        return $value;
    }

    public static function getValuesDummy(string $name): array {
        return array_merge(parent::getValuesDummy($name), [
            new DummyVariable($name.".hand", DummyVariable::ITEM),
            new DummyVariable($name.".food", DummyVariable::NUMBER),
        ]);
    }
}