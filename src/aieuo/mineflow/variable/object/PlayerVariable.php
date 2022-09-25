<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\BooleanVariable;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\StringVariable;
use pocketmine\player\Player;

class PlayerVariable extends HumanVariable {

    public static function getTypeName(): string {
        return "player";
    }

    public function __construct(Player $value) {
        parent::__construct($value);
    }

    public function __toString(): string {
        /** @var Player $player */
        $player = $this->getValue();
        return $player->getName();
    }

    public static function registerProperties(string $class = self::class): void {
        HumanVariable::registerProperties($class);

        self::registerProperty(
            $class, "name", new DummyVariable(StringVariable::class),
            fn(Player $player) => new StringVariable($player->getName()),
        );
        self::registerProperty(
            $class, "display_name", new DummyVariable(StringVariable::class),
            fn(Player $player) => new StringVariable($player->getDisplayName()),
        );
        self::registerProperty(
            $class, "locale", new DummyVariable(StringVariable::class),
            fn(Player $player) => new StringVariable($player->getLocale()),
        );
        self::registerProperty(
            $class, "ping", new DummyVariable(NumberVariable::class),
            fn(Player $player) => new NumberVariable($player->getNetworkSession()->getPing()),
        );
        self::registerProperty(
            $class, "ip", new DummyVariable(StringVariable::class),
            fn(Player $player) => new StringVariable($player->getNetworkSession()->getIp()),
        );
        self::registerProperty(
            $class, "port", new DummyVariable(NumberVariable::class),
            fn(Player $player) => new NumberVariable($player->getNetworkSession()->getPort()),
        );
        self::registerProperty(
            $class, "uuid", new DummyVariable(StringVariable::class),
            fn(Player $player) => new StringVariable($player->getUniqueId()->toString()),
        );
        self::registerProperty(
            $class, "spawn_point", new DummyVariable(PositionVariable::class),
            fn(Player $player) => new PositionVariable($player->getSpawn()),
        );
        self::registerProperty(
            $class, "flying", new DummyVariable(BooleanVariable::class),
            fn(Player $player) => new BooleanVariable($player->isFlying()),
        );
    }
}
