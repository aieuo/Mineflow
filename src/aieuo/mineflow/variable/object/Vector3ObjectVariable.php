<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\math\Vector3;

class Vector3ObjectVariable extends ObjectVariable {

    public function __construct(Vector3 $value, ?string $str = null) {
        parent::__construct($value, $str);
    }

    public function getValueFromIndex(string $index): ?Variable {
        $position = $this->getVector3();
        switch ($index) {
            case "x":
                $variable = new NumberVariable($position->x);
                break;
            case "y":
                $variable = new NumberVariable($position->y);
                break;
            case "z":
                $variable = new NumberVariable($position->z);
                break;
            case "xyz":
                $variable = new StringVariable($position->x.",".$position->y.",".$position->z);
                break;
            default:
                return null;
        }
        return $variable;
    }

    public function getVector3(): Vector3 {
        /** @var Vector3 $value */
        $value = $this->getValue();
        return $value;
    }

    public static function getValuesDummy(string $name): array {
        return array_merge(parent::getValuesDummy($name), [
            new DummyVariable($name.".x", DummyVariable::NUMBER),
            new DummyVariable($name.".y", DummyVariable::NUMBER),
            new DummyVariable($name.".z", DummyVariable::NUMBER),
            new DummyVariable($name.".xyz", DummyVariable::STRING),
        ]);
    }

    public function __toString(): string {
        $value = $this->getVector3();
        return $value->x.",".$value->y.",".$value->z;
    }
}