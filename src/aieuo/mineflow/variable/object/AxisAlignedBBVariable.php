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

    public function __construct(private AxisAlignedBB $aabb) {
    }

    public function getValue(): AxisAlignedBB {
        return $this->aabb;
    }

    public function getValueFromIndex(string $index): ?Variable {
        $aabb = $this->getValue();
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

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "min_x" => new DummyVariable(NumberVariable::class),
            "min_y" => new DummyVariable(NumberVariable::class),
            "min_z" => new DummyVariable(NumberVariable::class),
            "max_x" => new DummyVariable(NumberVariable::class),
            "max_y" => new DummyVariable(NumberVariable::class),
            "max_z" => new DummyVariable(NumberVariable::class),
            "min" => new DummyVariable(Vector3Variable::class),
            "max" => new DummyVariable(Vector3Variable::class),
        ]);
    }

    public function __toString(): string {
        return (string)$this->getValue();
    }
}
