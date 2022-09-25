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
use pocketmine\player\Player;
use pocketmine\world\World;
use function array_filter;
use function array_map;

class WorldVariable extends ObjectVariable {

    public static function getTypeName(): string {
        return "world";
    }

    public function __construct(private World $world) {
    }

    public function getValue(): World {
        return $this->world;
    }

    public function __toString(): string {
        return $this->getValue()->getFolderName();
    }

    public function getValueFromIndex(string $index): ?Variable {
        $level = $this->getValue();
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

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "name" => new DummyVariable(StringVariable::class),
            "folderName" => new DummyVariable(StringVariable::class),
            "id" => new DummyVariable(NumberVariable::class),
            "spawn" => new DummyVariable(PositionVariable::class),
            "safe_spawn" => new DummyVariable(PositionVariable::class),
            "time" => new DummyVariable(NumberVariable::class),
            "players" => new DummyVariable(ListVariable::class, PlayerVariable::getTypeName()),
            "entities" => new DummyVariable(ListVariable::class, EntityVariable::getTypeName()),
            "livings" => new DummyVariable(ListVariable::class, LivingVariable::getTypeName()),
        ]);
    }
}
