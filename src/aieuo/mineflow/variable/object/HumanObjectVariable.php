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
        return match ($index) {
            "hand" => new ItemObjectVariable($human->getInventory()->getItemInHand()),
            "food" => new NumberVariable($human->getFood()),
            "xp" => new NumberVariable($human->getCurrentTotalXp()),
            "xp_level" => new NumberVariable($human->getXpLevel()),
            "xp_progress" => new NumberVariable($human->getXpProgress()),
            default => null,
        };
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