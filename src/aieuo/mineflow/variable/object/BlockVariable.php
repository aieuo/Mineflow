<?php

declare(strict_types=1);

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

    public function __construct(private Block $block) {
        parent::__construct($this->block->getPosition());
    }

    public function getValue(): Block {
        return $this->block;
    }

    public function getValueFromIndex(string $index): ?Variable {
        $block = $this->getValue();
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

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "name" => new DummyVariable(StringVariable::class),
            "id" => new DummyVariable(NumberVariable::class),
            "damage" => new DummyVariable(NumberVariable::class),
            "meta" => new DummyVariable(NumberVariable::class),
            "item" => new DummyVariable(ItemVariable::class),
            "down" => new DummyVariable(BlockVariable::class),
            "up" => new DummyVariable(BlockVariable::class),
            "north" => new DummyVariable(BlockVariable::class),
            "south" => new DummyVariable(BlockVariable::class),
            "west" => new DummyVariable(BlockVariable::class),
            "east" => new DummyVariable(BlockVariable::class),
        ]);
    }

    public function __toString(): string {
        $value = $this->getValue();
        return $value->getId().":".$value->getMeta();
    }
}
