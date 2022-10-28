<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\exception\UnsupportedCalculationException;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use function array_merge;
use function max;
use function min;

class AxisAlignedBBObjectVariable extends ObjectVariable {

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

    public function add($target): AxisAlignedBBObjectVariable {
        $aabb = $this->getAxisAlignedBB();
        if ($target instanceof NumberVariable) {
            $value = $target->getValue();
            return new AxisAlignedBBObjectVariable($aabb->offsetCopy($value, $value, $value));
        }
        if ($target instanceof Vector3ObjectVariable) {
            $vector3 = $target->getVector3();
            return new AxisAlignedBBObjectVariable($aabb->offsetCopy($vector3->x, $vector3->y, $vector3->z));
        }
        if ($target instanceof AxisAlignedBBObjectVariable) {
            $target = $target->getAxisAlignedBB();
            return self::fromFloat(
                $aabb->minX + $target->minX, $aabb->minY + $target->minY, $aabb->minZ + $target->minZ,
                $aabb->maxX + $target->maxX, $aabb->maxY + $target->maxY, $aabb->maxZ + $target->maxZ,
            );
        }

        throw new UnsupportedCalculationException();
    }

    public function sub($target): AxisAlignedBBObjectVariable {
        $aabb = $this->getAxisAlignedBB();
        if ($target instanceof NumberVariable) {
            $value = $target->getValue();
            return new AxisAlignedBBObjectVariable($aabb->offsetCopy(-$value, -$value, -$value));
        }
        if ($target instanceof Vector3ObjectVariable) {
            $vector3 = $target->getVector3();
            return new AxisAlignedBBObjectVariable($aabb->offsetCopy(-$vector3->x, -$vector3->y, -$vector3->z));
        }
        if ($target instanceof AxisAlignedBBObjectVariable) {
            $target = $target->getAxisAlignedBB();
            return self::fromFloat(
                $aabb->minX - $target->minX, $aabb->minY - $target->minY, $aabb->minZ - $target->minZ,
                $aabb->maxX - $target->maxX, $aabb->maxY - $target->maxY, $aabb->maxZ - $target->maxZ,
            );
        }

        throw new UnsupportedCalculationException();
    }

    public function mul($target): AxisAlignedBBObjectVariable {
        if ($target instanceof NumberVariable) {
            $value = $target->getValue();
            return $this->extended($value, $value, $value, $value, $value, $value);
        }
        if ($target instanceof Vector3ObjectVariable) {
            $vector3 = $target->getVector3();
            return $this->extended($vector3->x, $vector3->y, $vector3->z, $vector3->x, $vector3->y, $vector3->z);
        }
        if ($target instanceof AxisAlignedBBObjectVariable) {
            $target = $target->getAxisAlignedBB();
            return $this->extended($target->minX, $target->minY, $target->minZ, $target->maxX, $target->maxY, $target->maxZ);
        }

        throw new UnsupportedCalculationException();
    }

    public function div($target): AxisAlignedBBObjectVariable {
        if ($target instanceof NumberVariable) {
            $value = $target->getValue();
            return $this->extended(-$value, -$value, -$value, -$value, -$value, -$value);
        }
        if ($target instanceof Vector3ObjectVariable) {
            $vector3 = $target->getVector3();
            return $this->extended(-$vector3->x, -$vector3->y, -$vector3->z, -$vector3->x, -$vector3->y, -$vector3->z);
        }
        if ($target instanceof AxisAlignedBBObjectVariable) {
            $target = $target->getAxisAlignedBB();
            return $this->extended(-$target->minX, -$target->minY, -$target->minZ, -$target->maxX, -$target->maxY, -$target->maxZ);
        }

        throw new UnsupportedCalculationException();
    }

    public function __toString(): string {
        return (string)$this->getAxisAlignedBB();
    }

    private function extended(float $minX, float $minY, float $minZ, float $maxX, float $maxY, float $maxZ): self {
        $aabb = $this->getAxisAlignedBB();
        return self::fromFloat(
            $aabb->minX - $minX, $aabb->minY - $minY, $aabb->minZ - $minZ,
            $aabb->maxX + $maxX, $aabb->maxY + $maxY, $aabb->maxZ + $maxZ,
        );
    }

    public static function fromFloat(float $x1, float $y1, float $z1, float $x2, float $y2, float $z2): AxisAlignedBBObjectVariable {
        return new AxisAlignedBBObjectVariable(new AxisAlignedBB(
            min($x1, $x2), min($y1, $y2), min($z1, $z2),
            max($x1, $x2), max($y1, $y2), max($z1, $z2),
        ));
    }

}
