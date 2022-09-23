<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\world\World;
use pocketmine\player\Player;
use function array_filter;
use function array_map;

class WorldVariable extends ObjectVariable {

    public function __construct(World $value, ?string $str = null) {
        parent::__construct($value, $str ?? $value->getFolderName());
    }

    public function getValueFromIndex(string $index): ?Variable {
        $level = $this->getWorld();
        return match ($index) {
            "name" => new StringVariable($level->getDisplayName()),
            "folderName" => new StringVariable($level->getFolderName()),
            "id" => new NumberVariable($level->getId()),
            "spawn" => new PositionVariable($level->getSpawnLocation()),
            "safe_spawn" => new PositionVariable($level->getSafeSpawn()),
            "time" => new NumberVariable($level->getTime()),
            "players" => new ListVariable(array_values(array_map(fn(Player $player) => new PlayerVariable($player), $level->getPlayers()))),
            "entities" => new ListVariable(array_values(array_map(fn(Entity $entity) => EntityVariable::fromObject($entity), $level->getEntities()))),
            "livings" => new ListVariable(array_values(array_map(fn(Living $living) => EntityVariable::fromObject($living),
                array_filter($level->getEntities(), fn(Entity $entity) => $entity instanceof Living)
            ))),
            default => parent::getValueFromIndex($index),
        };
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    public function getWorld(): World {
        return $this->getValue();
    }

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "name" => new DummyVariable(DummyVariable::STRING),
            "folderName" => new DummyVariable(DummyVariable::STRING),
            "id" => new DummyVariable(DummyVariable::NUMBER),
            "spawn" => new DummyVariable(DummyVariable::POSITION),
            "safe_spawn" => new DummyVariable(DummyVariable::POSITION),
            "time" => new DummyVariable(DummyVariable::NUMBER),
            "players" => new DummyVariable(DummyVariable::LIST, DummyVariable::PLAYER),
            "entities" => new DummyVariable(DummyVariable::LIST, DummyVariable::ENTITY),
            "livings" => new DummyVariable(DummyVariable::LIST, DummyVariable::LIVING),
        ]);
    }
}