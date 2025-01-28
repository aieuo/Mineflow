<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\exception\UnsupportedCalculationException;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use aieuo\mineflow\variable\VariableProperty;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;

class Vector3Variable extends ObjectVariable {

    public static function getTypeName(): string {
        return "vector3";
    }

    public function __construct(private Vector3 $vector3) {
    }

    public function getValue(): Vector3 {
        return $this->vector3;
    }

    public function add(Variable $target): Vector3Variable {
        $vector3 = $this->getValue();
        if ($target instanceof NumberVariable) {
            $value = $target->getValue();
            return new Vector3Variable($vector3->add($value, $value, $value));
        }
        if ($target instanceof Vector3Variable) {
            return new Vector3Variable($vector3->addVector($target->getValue()));
        }

        throw new UnsupportedCalculationException();
    }

    public function sub(Variable $target): Vector3Variable {
        $vector3 = $this->getValue();
        if ($target instanceof NumberVariable) {
            $value = $target->getValue();
            return new Vector3Variable($vector3->subtract($value, $value, $value));
        }
        if ($target instanceof Vector3Variable) {
            return new Vector3Variable($vector3->subtractVector($target->getValue()));
        }

        throw new UnsupportedCalculationException();
    }

    public function mul(Variable $target): Vector3Variable {
        $vector3 = $this->getValue();
        if ($target instanceof NumberVariable) {
            $value = $target->getValue();
            return new Vector3Variable(new Vector3($vector3->x * $value, $vector3->y * $value, $vector3->z * $value));
        }
        if ($target instanceof Vector3Variable) {
            $value = $target->getValue();
            return new Vector3Variable(new Vector3($vector3->x * $value->x, $vector3->y * $value->y, $vector3->z * $value->z));
        }

        throw new UnsupportedCalculationException();
    }

    public function div(Variable $target): Vector3Variable {
        $vector3 = $this->getValue();
        if ($target instanceof NumberVariable) {
            $value = $target->getValue();
            return new Vector3Variable(new Vector3($vector3->x / $value, $vector3->y / $value, $vector3->z / $value));
        }
        if ($target instanceof Vector3Variable) {
            $value = $target->getValue();
            return new Vector3Variable(new Vector3($vector3->x / $value->x, $vector3->y / $value->y, $vector3->z / $value->z));
        }

        throw new UnsupportedCalculationException();
    }

    public function __toString(): string {
        $value = $this->getValue();
        return $value->x.",".$value->y.",".$value->z;
    }

    public static function registerProperties(string $class = self::class): void {
        self::registerProperty($class, "x", new VariableProperty(
            new DummyVariable(NumberVariable::class),
            fn(Vector3 $position) => new NumberVariable($position->x),
        ));
        self::registerProperty($class, "y", new VariableProperty(
            new DummyVariable(NumberVariable::class),
            fn(Vector3 $position) => new NumberVariable($position->y),
        ));
        self::registerProperty($class, "z", new VariableProperty(
            new DummyVariable(NumberVariable::class),
            fn(Vector3 $position) => new NumberVariable($position->z),
        ));
        self::registerProperty($class, "xyz", new VariableProperty(
            new DummyVariable(StringVariable::class),
            fn(Vector3 $position) => new StringVariable($position->x.",".$position->y.",".$position->z),
        ));
        foreach (["down" => Facing::DOWN, "up" => Facing::UP, "north" => Facing::NORTH, "south" => Facing::SOUTH, "west" => Facing::WEST, "east" => Facing::EAST] as $name => $facing) {
            self::registerProperty($class, $name, new VariableProperty(
                new DummyVariable(Vector3Variable::class),
                fn(Vector3 $position) => new Vector3Variable($position->getSide($facing)),
            ));
        }
    }
}