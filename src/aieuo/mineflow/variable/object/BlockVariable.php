<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\Mineflow;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\VariableProperty;
use pocketmine\block\Block;
use pocketmine\math\Facing;

class BlockVariable extends ObjectVariable {

    private ?int $legacyId = null;
    private ?int $legacyMeta = null;

    private int $meta = 0;

    public static function getTypeName(): string {
        return "block";
    }

    public function __construct(private Block $block) {
        $this->initIds();
    }

    private function initIds(): void {
        $block = new \ReflectionClass(Block::class);
        $encodeFullState = $block->getMethod("encodeFullState");
        $this->meta = $encodeFullState->invoke($this->getValue());

        $ids = Mineflow::getBlockStateIdToLegacyIdMap()->get($this->getValue()->getStateId());
        if ($ids === null) return;

        $this->legacyId = $ids[0];
        $this->legacyMeta = $ids[1];
    }

    public function getValue(): Block {
        return $this->block;
    }

    public function getLegacyId(): ?int {
        return $this->legacyId;
    }

    public function getLegacyMeta(): ?int {
        return $this->legacyMeta;
    }

    public function getMeta(): int {
        return $this->meta;
    }

    public function __toString(): string {
        $value = $this->getValue();
        return ($this->getLegacyId() ?? $value->getTypeId()).":".($this->getLegacyMeta() ?? $this->getMeta());
    }

    public static function registerProperties(string $class = self::class): void {
        self::registerProperty($class, "name", new VariableProperty(
            new DummyVariable(StringVariable::class),
            fn(Block $block) => new StringVariable($block->getName()),
        ));
        self::registerProperty($class, "id", new VariableProperty(
            new DummyVariable(NumberVariable::class),
            fn(BlockVariable $var) => new NumberVariable($var->getLegacyId() ?? $var->getValue()->getTypeId()),
            passVariable: true
        ));
        self::registerProperty($class, "damage", new VariableProperty(
            new DummyVariable(NumberVariable::class),
            fn(BlockVariable $var) => new NumberVariable($var->getLegacyMeta() ?? $var->getMeta()),
            passVariable: true
        ), aliases:["meta"]);
        self::registerProperty($class, "item", new VariableProperty(
            new DummyVariable(ItemVariable::class),
            fn(Block $block) => new ItemVariable($block->getPickedItem()),
        ));
        self::registerProperty($class, "position", new VariableProperty(
            new DummyVariable(PositionVariable::class),
            fn(Block $block) => new PositionVariable($block->getPosition()->asPosition()),
        ));
        self::registerProperty($class, "world", new VariableProperty(
            new DummyVariable(WorldVariable::class),
            fn(Block $block) => new WorldVariable($block->getPosition()->getWorld()),
        ));
        self::registerProperty($class, "x", new VariableProperty(
            new DummyVariable(NumberVariable::class),
            fn(Block $block) => new NumberVariable($block->getPosition()->getX()),
        ));
        self::registerProperty($class, "y", new VariableProperty(
            new DummyVariable(NumberVariable::class),
            fn(Block $block) => new NumberVariable($block->getPosition()->getY()),
        ));
        self::registerProperty($class, "z", new VariableProperty(
            new DummyVariable(NumberVariable::class),
            fn(Block $block) => new NumberVariable($block->getPosition()->getZ()),
        ));
        self::registerProperty($class, "xyz", new VariableProperty(
            new DummyVariable(StringVariable::class),
            fn(Block $block) => new StringVariable($block->getPosition()->getX().",".$block->getPosition()->getY().",".$block->getPosition()->getZ()),
        ));
        foreach (["down" => Facing::DOWN, "up" => Facing::UP, "north" => Facing::NORTH, "south" => Facing::SOUTH, "west" => Facing::WEST, "east" => Facing::EAST] as $name => $facing) {
            self::registerProperty($class, $name, new VariableProperty(
                new DummyVariable(BlockVariable::class),
                fn(Block $block) => new BlockVariable($block->getSide($facing)),
            ));
        }
    }
}