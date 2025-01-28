<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\ObjectVariable;
use pocketmine\player\GameMode;

class GameModeVariable extends ObjectVariable {

    public static function getTypeName(): string {
        return "GameMode";
    }

    public function __construct(private GameMode $gameMode) {
    }

    public function getValue(): GameMode {
        return $this->gameMode;
    }

    public function __toString(): string {
        return $this->getValue()->getEnglishName();
    }

    // public static function registerProperties(string $class = self::class): void {
    //     self::registerProperty($class, "name", new VariableProperty(
    //         new DummyVariable(StringVariable::class),
    //         fn(World $world) => new StringVariable($world->getDisplayName()),
    //     ));
    //     self::registerProperty($class, "folderName", new VariableProperty(
    //         new DummyVariable(StringVariable::class),
    //         fn(World $world) => new StringVariable($world->getFolderName()),
    //     ));
    //     self::registerProperty($class, "id", new VariableProperty(
    //         new DummyVariable(NumberVariable::class),
    //         fn(World $world) => new NumberVariable($world->getId()),
    //     ));
    //     self::registerProperty($class, "spawn", new VariableProperty(
    //         new DummyVariable(PositionVariable::class),
    //         fn(World $world) => new PositionVariable($world->getSpawnLocation()),
    //     ));
    //     self::registerProperty($class, "safe_spawn", new VariableProperty(
    //         new DummyVariable(PositionVariable::class),
    //         fn(World $world) => new PositionVariable($world->getSafeSpawn()),
    //     ));
    //     self::registerProperty($class, "time", new VariableProperty(
    //         new DummyVariable(NumberVariable::class),
    //         fn(World $world) => new NumberVariable($world->getTime()),
    //     ));
    //     self::registerProperty($class, "players", new VariableProperty(
    //         new DummyVariable(ListVariable::class, PlayerVariable::getTypeName()),
    //         fn(World $world) => new ListVariable(array_values(array_map(fn(Player $player) => new PlayerVariable($player), $world->getPlayers()))),
    //     ));
    //     self::registerProperty($class, "entities", new VariableProperty(
    //         new DummyVariable(ListVariable::class, EntityVariable::getTypeName()),
    //         fn(World $world) => new ListVariable(array_values(array_map(fn(Entity $entity) => EntityVariable::fromObject($entity), $world->getEntities()))),
    //     ));
    //     self::registerProperty($class, "livings", new VariableProperty(
    //         new DummyVariable(ListVariable::class, LivingVariable::getTypeName()),
    //         fn($world) => new ListVariable(array_values(array_map(fn(Living $living) => EntityVariable::fromObject($living),
    //             array_filter($world->getEntities(), fn(Entity $entity) => $entity instanceof Living)
    //         ))),
    //     ));
    // }
}
