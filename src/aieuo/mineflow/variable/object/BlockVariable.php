<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\block\Block;
use pocketmine\math\Facing;

class BlockVariable extends PositionVariable {

    public static function getTypeName(): string {
        return "block";
    }

    public function __construct(private Block $block, ?string $str = null) {
        parent::__construct($block->getPosition(), $str);
    }

    public function getValueFromIndex(string $index): ?Variable {
        $block = $this->getBlock();
        return match ($index) {
            "name" => new StringVariable($block->getName()),
            "id" => new NumberVariable($block->getId()),
            "damage", "meta" => new NumberVariable($block->getMeta()),
            "item" => new ItemVariable($block->getPickedItem()),
            "down" => new BlockVariable($block->getSide(Facing::DOWN)),
            "up" => new BlockVariable($block->getSide(Facing::UP)),
            "north" => new BlockVariable($block->getSide(Facing::NORTH)),
            "south" => new BlockVariable($block->getSide(Facing::SOUTH)),
            "west" => new BlockVariable($block->getSide(Facing::WEST)),
            "east" => new BlockVariable($block->getSide(Facing::EAST)),
            default => parent::getValueFromIndex($index),
        };
    }

    public function getBlock(): Block {
        return $this->block;
    }

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "name" => new DummyVariable(DummyVariable::STRING),
            "id" => new DummyVariable(DummyVariable::NUMBER),
            "damage" => new DummyVariable(DummyVariable::NUMBER),
            "meta" => new DummyVariable(DummyVariable::NUMBER),
            "item" => new DummyVariable(DummyVariable::ITEM),
            "down" => new DummyVariable(DummyVariable::BLOCK),
            "up" => new DummyVariable(DummyVariable::BLOCK),
            "north" => new DummyVariable(DummyVariable::BLOCK),
            "south" => new DummyVariable(DummyVariable::BLOCK),
            "west" => new DummyVariable(DummyVariable::BLOCK),
            "east" => new DummyVariable(DummyVariable::BLOCK),
        ]);
    }

    public function __toString(): string {
        $value = $this->getBlock();
        return (string)$value;
    }
}
