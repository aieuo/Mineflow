<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\exception\UnsupportedCalculationException;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\VariableProperty;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use function max;
use function min;

class AxisAlignedBBVariable extends ObjectVariable {

    public static function getTypeName(): string {
        return "aabb";
    }

    public function __construct(private AxisAlignedBB $aabb) {
    }

    public function getValue(): AxisAlignedBB {
        return $this->aabb;
    }

    public function add($target): AxisAlignedBBVariable {
        $aabb = $this->getValue();
        if ($target instanceof NumberVariable) {
            $value = $target->getValue();
            return new AxisAlignedBBVariable($aabb->offsetCopy($value, $value, $value));
        }
        if ($target instanceof Vector3Variable) {
            $vector3 = $target->getValue();
            return new AxisAlignedBBVariable($aabb->offsetCopy($vector3->x, $vector3->y, $vector3->z));
        }
        if ($target instanceof AxisAlignedBBVariable) {
            $target = $target->getValue();
            return self::fromFloat(
                $aabb->minX + $target->minX, $aabb->minY + $target->minY, $aabb->minZ + $target->minZ,
                $aabb->maxX + $target->maxX, $aabb->maxY + $target->maxY, $aabb->maxZ + $target->maxZ,
            );
        }

        throw new UnsupportedCalculationException();
    }

    public function sub($target): AxisAlignedBBVariable {
        $aabb = $this->getValue();
        if ($target instanceof NumberVariable) {
            $value = $target->getValue();
            return new AxisAlignedBBVariable($aabb->offsetCopy(-$value, -$value, -$value));
        }
        if ($target instanceof Vector3Variable) {
            $vector3 = $target->getValue();
            return new AxisAlignedBBVariable($aabb->offsetCopy(-$vector3->x, -$vector3->y, -$vector3->z));
        }
        if ($target instanceof AxisAlignedBBVariable) {
            $target = $target->getValue();
            return self::fromFloat(
                $aabb->minX - $target->minX, $aabb->minY - $target->minY, $aabb->minZ - $target->minZ,
                $aabb->maxX - $target->maxX, $aabb->maxY - $target->maxY, $aabb->maxZ - $target->maxZ,
            );
        }

        throw new UnsupportedCalculationException();
    }

    public function mul($target): AxisAlignedBBVariable {
        if ($target instanceof NumberVariable) {
            $value = $target->getValue();
            return $this->extended($value, $value, $value, $value, $value, $value);
        }
        if ($target instanceof Vector3Variable) {
            $vector3 = $target->getValue();
            return $this->extended($vector3->x, $vector3->y, $vector3->z, $vector3->x, $vector3->y, $vector3->z);
        }
        if ($target instanceof AxisAlignedBBVariable) {
            $target = $target->getValue();
            return $this->extended($target->minX, $target->minY, $target->minZ, $target->maxX, $target->maxY, $target->maxZ);
        }

        throw new UnsupportedCalculationException();
    }

    public function div($target): AxisAlignedBBVariable {
        if ($target instanceof NumberVariable) {
            $value = $target->getValue();
            return $this->extended(-$value, -$value, -$value, -$value, -$value, -$value);
        }
        if ($target instanceof Vector3Variable) {
            $vector3 = $target->getValue();
            return $this->extended(-$vector3->x, -$vector3->y, -$vector3->z, -$vector3->x, -$vector3->y, -$vector3->z);
        }
        if ($target instanceof AxisAlignedBBVariable) {
            $target = $target->getValue();
            return $this->extended(-$target->minX, -$target->minY, -$target->minZ, -$target->maxX, -$target->maxY, -$target->maxZ);
        }

        throw new UnsupportedCalculationException();
    }

    public function __toString(): string {
        return (string)$this->getValue();
    }

    public static function registerProperties(string $class = self::class): void {
        self::registerProperty($class, "min_x", new VariableProperty(
            new DummyVariable(NumberVariable::class),
            fn(AxisAlignedBB $aabb) => new NumberVariable($aabb->minX),
        ));
        self::registerProperty($class, "min_y", new VariableProperty(
            new DummyVariable(NumberVariable::class),
            fn(AxisAlignedBB $aabb) => new NumberVariable($aabb->minY),
        ));
        self::registerProperty($class, "min_z", new VariableProperty(
            new DummyVariable(NumberVariable::class),
            fn(AxisAlignedBB $aabb) => new NumberVariable($aabb->minZ),
        ));
        self::registerProperty($class, "max_x", new VariableProperty(
            new DummyVariable(NumberVariable::class),
            fn(AxisAlignedBB $aabb) => new NumberVariable($aabb->maxX),
        ));
        self::registerProperty($class, "max_y", new VariableProperty(
            new DummyVariable(NumberVariable::class),
            fn(AxisAlignedBB $aabb) => new NumberVariable($aabb->maxY),
        ));
        self::registerProperty($class, "max_z", new VariableProperty(
            new DummyVariable(NumberVariable::class),
            fn(AxisAlignedBB $aabb) => new NumberVariable($aabb->maxZ),
        ));
        self::registerProperty($class, "min", new VariableProperty(
            new DummyVariable(Vector3Variable::class),
            fn(AxisAlignedBB $aabb) => new Vector3($aabb->minX, $aabb->minY, $aabb->minZ),
        ));
        self::registerProperty($class, "max", new VariableProperty(
            new DummyVariable(Vector3Variable::class),
            fn(AxisAlignedBB $aabb) => new Vector3($aabb->maxX, $aabb->maxY, $aabb->maxZ),
        ));
    }

    private function extended(float $minX, float $minY, float $minZ, float $maxX, float $maxY, float $maxZ): self {
        $aabb = $this->getValue();
        return self::fromFloat(
            $aabb->minX - $minX, $aabb->minY - $minY, $aabb->minZ - $minZ,
            $aabb->maxX + $maxX, $aabb->maxY + $maxY, $aabb->maxZ + $maxZ,
        );
    }

    public static function fromFloat(float $x1, float $y1, float $z1, float $x2, float $y2, float $z2): AxisAlignedBBVariable {
        return new AxisAlignedBBVariable(new AxisAlignedBB(
            min($x1, $x2), min($y1, $y2), min($z1, $z2),
            max($x1, $x2), max($y1, $y2), max($z1, $z2),
        ));
    }
}