<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\BooleanVariable;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\player\Player;

class PlayerVariable extends HumanVariable {

    public static function getTypeName(): string {
        return "player";
    }

    public function __construct(Player $value) {
        parent::__construct($value);
    }

    public function getValueFromIndex(string $index): ?Variable {
        /** @var Player $player */
        $player = $this->getValue();
        return match ($index) {
            "name" => new StringVariable($player->getName()),
            "display_name" => new StringVariable($player->getDisplayName()),
            "locale" => new StringVariable($player->getLocale()),
            "ping" => new NumberVariable($player->getNetworkSession()->getPing()),
            "ip" => new StringVariable($player->getNetworkSession()->getIp()),
            "port" => new NumberVariable($player->getNetworkSession()->getPort()),
            "uuid" => new StringVariable($player->getUniqueId()->toString()),
            "spawn_point" => new PositionVariable($player->getSpawn()),
            "flying" => new BooleanVariable($player->isFlying()),
            default => parent::getValueFromIndex($index),
        };
    }

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "name" => new DummyVariable(StringVariable::class),
            "display_name" => new DummyVariable(StringVariable::class),
            "locale" => new DummyVariable(StringVariable::class),
            "ping" => new DummyVariable(NumberVariable::class),
            "ip" => new DummyVariable(StringVariable::class),
            "port" => new DummyVariable(NumberVariable::class),
            "uuid" => new DummyVariable(StringVariable::class),
            "spawn_point" => new DummyVariable(PositionVariable::class),
            "flying" => new DummyVariable(BooleanVariable::class),
        ]);
    }

    public function __toString(): string {
        /** @var Player $player */
        $player = $this->getValue();
        return $player->getName();
    }
}
