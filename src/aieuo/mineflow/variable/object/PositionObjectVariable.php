<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\level\Position;

class PositionObjectVariable extends Vector3ObjectVariable {

    public function __construct(Position $value, ?string $str = null) {
        parent::__construct($value, $str);
    }

    public function getValueFromIndex(string $index): ?Variable {
        $variable = parent::getValueFromIndex($index);
        if ($variable !== null) return $variable;

        $position = $this->getPosition();
        switch ($index) {
            case "position":
                $variable = new PositionObjectVariable($position);
                break;
            case "world":
                $variable = new WorldObjectVariable($position->level, $position->level->getFolderName());
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
            "world" => new DummyVariable(DummyVariable::WORLD),
        ]);
    }

    public function __toString(): string {
        $value = $this->getPosition();
        return $value->x.",".$value->y.",".$value->z.",".$value->level->getFolderName();
    }
}