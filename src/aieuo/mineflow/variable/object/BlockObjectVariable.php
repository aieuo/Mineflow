<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\block\Block;

class BlockObjectVariable extends PositionObjectVariable {

    public function __construct(Block $value, ?string $str = null) {
        parent::__construct($value, $str);
    }

    public function getValueFromIndex(string $index): ?Variable {
        $variable = parent::getValueFromIndex($index);
        if ($variable !== null) return $variable;

        $block = $this->getBlock();
        return match ($index) {
            "name" => new StringVariable($block->getName()),
            "id" => new NumberVariable($block->getId()),
            "damage" => new NumberVariable($block->getDamage()),
            "item" => new ItemObjectVariable($block->getPickedItem()),
            default => null,
        };
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    public function getBlock(): Block {
        return $this->getValue();
    }

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "name" => new DummyVariable(DummyVariable::STRING),
            "id" => new DummyVariable(DummyVariable::NUMBER),
            "damage" => new DummyVariable(DummyVariable::NUMBER),
        ]);
    }

    public function __toString(): string {
        $value = $this->getBlock();
        return (string)$value;
    }
}