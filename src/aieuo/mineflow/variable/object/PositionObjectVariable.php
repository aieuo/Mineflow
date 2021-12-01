<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\world\Position;

class PositionObjectVariable extends Vector3ObjectVariable {

    public function __construct(Position $value, ?string $str = null) {
        parent::__construct($value, $str);
    }

    public function getProperty(string $name): ?Variable {
        $variable = parent::getProperty($name);
        if ($variable !== null) return $variable;

        $position = $this->getPosition();
        return match ($name) {
            "position" => new PositionObjectVariable($position),
            "world" => new WorldObjectVariable($position->world, $position->world->getFolderName()),
            default => null,
        };
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    public function getPosition(): Position {
        return $this->getValue();
    }

    public static function getTypeName(): string {
        return "position";
    }

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "world" => new DummyVariable(WorldObjectVariable::class),
        ]);
    }

    public function __toString(): string {
        $value = $this->getPosition();
        return $value->x.",".$value->y.",".$value->z.",".$value->world->getFolderName();
    }
}