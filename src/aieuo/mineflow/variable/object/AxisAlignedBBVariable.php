<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use function array_merge;

class AxisAlignedBBVariable extends ObjectVariable {

    public static function getTypeName(): string {
        return "aabb";
    }

    public function __construct(AxisAlignedBB $value, ?string $str = null) {
        parent::__construct($value, $str);
    }

    public function getValueFromIndex(string $index): ?Variable {
        $aabb = $this->getAxisAlignedBB();
        return match ($index) {
            "min_x" => new NumberVariable($aabb->minX),
            "min_y" => new NumberVariable($aabb->minY),
            "min_Z" => new NumberVariable($aabb->minZ),
            "max_x" => new NumberVariable($aabb->maxX),
            "max_y" => new NumberVariable($aabb->maxY),
            "max_Z" => new NumberVariable($aabb->maxZ),
            "min" => new Vector3($aabb->minX, $aabb->minY, $aabb->minZ),
            "max" => new Vector3($aabb->maxX, $aabb->maxY, $aabb->maxZ),
            default => parent::getValueFromIndex($index),
        };
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    public function getAxisAlignedBB(): AxisAlignedBB {
        return $this->getValue();
    }

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "min_x" => new DummyVariable(DummyVariable::NUMBER),
            "min_y" => new DummyVariable(DummyVariable::NUMBER),
            "min_z" => new DummyVariable(DummyVariable::NUMBER),
            "max_x" => new DummyVariable(DummyVariable::NUMBER),
            "max_y" => new DummyVariable(DummyVariable::NUMBER),
            "max_z" => new DummyVariable(DummyVariable::NUMBER),
            "min" => new DummyVariable(DummyVariable::VECTOR3),
            "max" => new DummyVariable(DummyVariable::VECTOR3),
        ]);
    }

    public function __toString(): string {
        return (string)$this->getAxisAlignedBB();
    }
}
