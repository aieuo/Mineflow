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

    public function getValueFromIndex(string $index): ?Variable {
        $position = $this->getPosition();
        return match ($index) {
            "position" => new PositionObjectVariable($position),
            "world" => new WorldObjectVariable($position->world, $position->world->getFolderName()),
            "down" => new PositionObjectVariable(Position::fromObject($position->down(1), $position->world)),
            "up" => new PositionObjectVariable(Position::fromObject($position->up(1), $position->world)),
            "north" => new PositionObjectVariable(Position::fromObject($position->north(1), $position->world)),
            "south" => new PositionObjectVariable(Position::fromObject($position->south(1), $position->world)),
            "west" => new PositionObjectVariable(Position::fromObject($position->west(1), $position->world)),
            "east" => new PositionObjectVariable(Position::fromObject($position->east(1), $position->world)),
            default => parent::getValueFromIndex($index),
        };
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    public function getPosition(): Position {
        return $this->getValue();
    }

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "position" => new DummyVariable(DummyVariable::POSITION),
            "world" => new DummyVariable(DummyVariable::WORLD),
            "down" => new DummyVariable(DummyVariable::POSITION),
            "up" => new DummyVariable(DummyVariable::POSITION),
            "north" => new DummyVariable(DummyVariable::POSITION),
            "south" => new DummyVariable(DummyVariable::POSITION),
            "west" => new DummyVariable(DummyVariable::POSITION),
            "east" => new DummyVariable(DummyVariable::POSITION),
        ]);
    }

    public function __toString(): string {
        $value = $this->getPosition();
        return $value->x.",".$value->y.",".$value->z.",".$value->world->getFolderName();
    }
}