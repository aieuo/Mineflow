<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\ObjectVariable;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;

class AxisAlignedBBVariable extends ObjectVariable {

    public static function getTypeName(): string {
        return "aabb";
    }

    public function __construct(private AxisAlignedBB $aabb) {
    }

    public function getValue(): AxisAlignedBB {
        return $this->aabb;
    }

    public function __toString(): string {
        return (string)$this->getValue();
    }

    public static function registerProperties(string $class = self::class): void {
        self::registerProperty(
            $class, "min_x", new DummyVariable(NumberVariable::class),
            fn(AxisAlignedBB $aabb) => new NumberVariable($aabb->minX),
        );
        self::registerProperty(
            $class, "min_y", new DummyVariable(NumberVariable::class),
            fn(AxisAlignedBB $aabb) => new NumberVariable($aabb->minY),
        );
        self::registerProperty(
            $class, "min_z", new DummyVariable(NumberVariable::class),
            fn(AxisAlignedBB $aabb) => new NumberVariable($aabb->minZ),
        );
        self::registerProperty(
            $class, "max_x", new DummyVariable(NumberVariable::class),
            fn(AxisAlignedBB $aabb) => new NumberVariable($aabb->maxX),
        );
        self::registerProperty(
            $class, "max_y", new DummyVariable(NumberVariable::class),
            fn(AxisAlignedBB $aabb) => new NumberVariable($aabb->maxY),
        );
        self::registerProperty(
            $class, "max_z", new DummyVariable(NumberVariable::class),
            fn(AxisAlignedBB $aabb) => new NumberVariable($aabb->maxZ),
        );
        self::registerProperty(
            $class, "min", new DummyVariable(Vector3Variable::class),
            fn(AxisAlignedBB $aabb) => new Vector3($aabb->minX, $aabb->minY, $aabb->minZ),
        );
        self::registerProperty(
            $class, "max", new DummyVariable(Vector3Variable::class),
            fn(AxisAlignedBB $aabb) => new Vector3($aabb->maxX, $aabb->maxY, $aabb->maxZ),
        );
    }
}
