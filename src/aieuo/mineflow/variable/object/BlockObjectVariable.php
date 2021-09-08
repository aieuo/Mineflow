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

    public static function getTypeName(): string {
        return "block";
    }

    public function getProperty(string $name): ?Variable {
        $variable = parent::getProperty($name);
        if ($variable !== null) return $variable;

        $block = $this->getBlock();
        switch ($name) {
            case "name":
                return new StringVariable($block->getName());
            case "id":
                return new NumberVariable($block->getId());
            case "damage":
                return new NumberVariable($block->getDamage());
            case "item":
                return new ItemObjectVariable($block->getPickedItem());
            default:
                return null;
        }
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    public function getBlock(): Block {
        return $this->getValue();
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