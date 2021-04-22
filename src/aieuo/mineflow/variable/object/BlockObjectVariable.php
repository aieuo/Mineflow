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
        switch ($index) {
            case "name":
                $variable = new StringVariable($block->getName());
                break;
            case "id":
                $variable = new NumberVariable($block->getId());
                break;
            case "damage":
                $variable = new NumberVariable($block->getDamage());
                break;
            default:
                return null;
        }
        return $variable;
    }

    public function getBlock(): Block {
        /** @var Block $value */
        $value = $this->getValue();
        return $value;
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