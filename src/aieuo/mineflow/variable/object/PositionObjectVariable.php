<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\level\Position;

class PositionObjectVariable extends Vector3ObjectVariable {

    public function __construct(Position $value, ?string $str = null) {
        parent::__construct($value, $str);
    }

    public function getValueFromIndex(string $index): ?Variable {
        $position = $this->getPosition();
        switch ($index) {
            case "position":
                $variable = new PositionObjectVariable($position);
                break;
            case "level":
            case "world":
                $variable = new LevelObjectVariable($position->level, $position->level->getFolderName());
                break;
            default:
                return null;
        }
        return $variable;
    }

    public function getPosition(): Position {
        /** @var Position $value */
        $value = $this->getValue();
        return $value;
    }

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "level" => new DummyVariable(DummyVariable::LEVEL),
            "world" => new DummyVariable(DummyVariable::LEVEL),
        ]);
    }

    public function __toString(): string {
        $value = $this->getPosition();
        return $value->x.",".$value->y.",".$value->z.",".$value->level->getFolderName();
    }
}