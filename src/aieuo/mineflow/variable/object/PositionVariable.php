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

    public function __construct(Position $value) {
        parent::__construct($value);
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
            "world" => new DummyVariable(WorldVariable::class),
            "down" => new DummyVariable(PositionVariable::class),
            "up" => new DummyVariable(PositionVariable::class),
            "north" => new DummyVariable(PositionVariable::class),
            "south" => new DummyVariable(PositionVariable::class),
            "west" => new DummyVariable(PositionVariable::class),
            "east" => new DummyVariable(PositionVariable::class),
        ]);
    }

    public function __toString(): string {
        /** @var Position $position */
        $value = $this->getValue();
        return $value->x.",".$value->y.",".$value->z.",".$value->world->getFolderName();
    }
}
