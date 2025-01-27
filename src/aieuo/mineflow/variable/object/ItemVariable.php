<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\Mineflow;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\MapVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\VariableProperty;
use pocketmine\item\Item;
use function array_map;

class ItemVariable extends ObjectVariable {

    private ?int $legacyId = null;
    private ?int $legacyMeta = null;

    private int $meta = 0;

    public static function getTypeName(): string {
        return "item";
    }

    public function __construct(private Item $item) {
        $this->initIds();
    }

    private function initIds(): void {
        $item = new \ReflectionClass(Item::class);
        $encodeFullState = $item->getMethod("computeStateData");
        $this->meta = $encodeFullState->invoke($this->getValue());

        $ids = Mineflow::getItemStateIdToLegacyIdMap()->get($this->getValue()->getStateId());
        if ($ids === null) return;

        $this->legacyId = $ids[0];
        $this->legacyMeta = $ids[1];
    }

    public function getValue(): Item {
        return $this->item;
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

    public function setItem(Item $item): void {
        $this->item = $item;
        $this->initIds();
    }

    public function __toString(): string {
        $item = $this->getValue();
        return "Item[".$item->getName()."] (".($this->getLegacyId() ?? $item->getTypeId()).":".($this->getLegacyMeta() ?? $this->getMeta()).")x".$item->getCount();
    }

    public static function registerProperties(string $class = self::class): void {
        self::registerProperty($class, "name", new VariableProperty(
            new DummyVariable(StringVariable::class),
            fn(Item $item) => new StringVariable($item->getName()),
        ));
        self::registerProperty($class, "vanilla_name", new VariableProperty(
            new DummyVariable(StringVariable::class),
            fn(Item $item) => new StringVariable($item->getVanillaName()),
        ));
        self::registerProperty($class, "custom_name", new VariableProperty(
            new DummyVariable(StringVariable::class),
            fn(Item $item) => new StringVariable($item->getCustomName()),
        ));
        self::registerProperty($class, "id", new VariableProperty(
            new DummyVariable(NumberVariable::class),
            fn(ItemVariable $var) => new NumberVariable($var->getLegacyId() ?? $var->getValue()->getTypeId()),
            passVariable: true
        ));
        self::registerProperty($class, "damage", new VariableProperty(
            new DummyVariable(NumberVariable::class),
            fn(ItemVariable $var) => new NumberVariable($var->getLegacyMeta() ?? $var->getMeta()),
            passVariable: true
        ), aliases: ["meta"]);
        self::registerProperty($class, "count", new VariableProperty(
            new DummyVariable(NumberVariable::class),
            fn(Item $item) => new NumberVariable($item->getCount()),
        ));
        self::registerProperty($class, "lore", new VariableProperty(
            new DummyVariable(ListVariable::class, StringVariable::getTypeName()),
            fn(Item $item) => new ListVariable(array_map(fn(string $lore) => new StringVariable($lore), $item->getLore())),
        ));
        self::registerProperty($class, "block", new VariableProperty(
            new DummyVariable(BlockVariable::class),
            fn(Item $item) => new BlockVariable($item->getBlock()),
        ));
        self::registerProperty($class, "tag", new VariableProperty(
            new DummyVariable(MapVariable::class),
            fn(Item $item) => Mineflow::getVariableHelper()->tagToVariable($item->getNamedTag()),
        ));
    }
}