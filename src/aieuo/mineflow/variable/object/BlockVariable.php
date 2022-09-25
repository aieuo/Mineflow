<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\StringVariable;
use pocketmine\block\Block;
use pocketmine\math\Facing;

class BlockVariable extends ObjectVariable {

    public static function getTypeName(): string {
        return "block";
    }

    public function __construct(private Block $block) {
    }

    public function getValue(): Block {
        return $this->block;
    }

    public function __toString(): string {
        $value = $this->getValue();
        return $value->getId().":".$value->getMeta();
    }

    public static function registerProperties(string $class = self::class): void {
        self::registerProperty(
            $class, "name", new DummyVariable(StringVariable::class),
            fn(Block $block) => new StringVariable($block->getName()),
        );
        self::registerProperty(
            $class, "id", new DummyVariable(NumberVariable::class),
            fn(Block $block) => new NumberVariable($block->getId()),
        );
        self::registerProperty(
            $class, "damage", new DummyVariable(NumberVariable::class),
            fn(Block $block) => new NumberVariable($block->getMeta()),
            aliases: ["meta"],
        );
        self::registerProperty(
            $class, "item", new DummyVariable(ItemVariable::class),
            fn(Block $block) => new ItemVariable($block->getPickedItem()),
        );
        self::registerProperty(
            $class, "position", new DummyVariable(LocationVariable::class),
            fn(Block $block) => new PositionVariable($block->getPosition()->asPosition()),
        );
        self::registerProperty(
            $class, "world", new DummyVariable(WorldVariable::class),
            fn(Block $block) => new WorldVariable($block->getPosition()->getWorld()),
        );
        self::registerProperty(
            $class, "x", new DummyVariable(NumberVariable::class),
            fn(Block $block) => new NumberVariable($block->getPosition()->getX()),
        );
        self::registerProperty(
            $class, "y", new DummyVariable(NumberVariable::class),
            fn(Block $block) => new NumberVariable($block->getPosition()->getY()),
        );
        self::registerProperty(
            $class, "z", new DummyVariable(NumberVariable::class),
            fn(Block $block) => new NumberVariable($block->getPosition()->getZ()),
        );
        self::registerProperty(
            $class, "xyz", new DummyVariable(StringVariable::class),
            fn(Block $block) => new StringVariable($block->getPosition()->getX().",".$block->getPosition()->getY().",".$block->getPosition()->getZ()),
        );
        foreach (["down" => Facing::DOWN, "up" => Facing::UP, "north" => Facing::NORTH, "south" => Facing::SOUTH, "west" => Facing::WEST, "east" => Facing::EAST] as $name => $facing) {
            self::registerProperty(
                $class, $name, new DummyVariable(BlockVariable::class),
                fn(Block $block) => new BlockVariable($block->getSide($facing)),
            );
        }
    }
}
