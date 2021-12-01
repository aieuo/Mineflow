<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\block\Block;

class BlockObjectVariable extends PositionObjectVariable {

    public function __construct(private Block $block, ?string $str = null) {
        parent::__construct($block->getPosition(), $str);
    }

    public static function getTypeName(): string {
        return "block";
    }

    public function getProperty(string $name): ?Variable {
        $variable = parent::getProperty($name);
        if ($variable !== null) return $variable;

        $block = $this->getBlock();
        return match ($name) {
            "name" => new StringVariable($block->getName()),
            "id" => new NumberVariable($block->getId()),
            "damage" => new NumberVariable($block->getMeta()),
            "item" => new ItemObjectVariable($block->getPickedItem()),
            default => null,
        };
    }

    public function getBlock(): Block {
        return $this->block;
    }

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "name" => new DummyVariable(StringVariable::class),
            "id" => new DummyVariable(NumberVariable::class),
            "damage" => new DummyVariable(NumberVariable::class),
        ]);
    }

    public function __toString(): string {
        $value = $this->getBlock();
        return (string)$value;
    }
}