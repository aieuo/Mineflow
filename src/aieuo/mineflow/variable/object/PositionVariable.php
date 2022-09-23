<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\world\Position;

class PositionVariable extends Vector3Variable {

    public static function getTypeName(): string {
        return "position";
    }

    public function __construct(Position $value, ?string $str = null) {
        parent::__construct($value, $str);
    }

    public function getValueFromIndex(string $index): ?Variable {
        /** @var Position $position */
        $position = $this->getValue();
        return match ($index) {
            "position" => new PositionVariable($position),
            "world" => new WorldVariable($position->world, $position->world->getFolderName()),
            "down" => new PositionVariable(Position::fromObject($position->down(1), $position->world)),
            "up" => new PositionVariable(Position::fromObject($position->up(1), $position->world)),
            "north" => new PositionVariable(Position::fromObject($position->north(1), $position->world)),
            "south" => new PositionVariable(Position::fromObject($position->south(1), $position->world)),
            "west" => new PositionVariable(Position::fromObject($position->west(1), $position->world)),
            "east" => new PositionVariable(Position::fromObject($position->east(1), $position->world)),
            default => parent::getValueFromIndex($index),
        };
    }

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
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
        /** @var Position $position */
        $value = $this->getValue();
        return $value->x.",".$value->y.",".$value->z.",".$value->world->getFolderName();
    }
}
