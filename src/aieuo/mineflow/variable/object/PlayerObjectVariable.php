<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\BoolVariable;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\player\Player;

class PlayerObjectVariable extends HumanObjectVariable {

    public function __construct(Player $value, ?string $str = null) {
        parent::__construct($value, $str ?? $value->getName());
    }

    public function getValueFromIndex(string $index): ?Variable {
        $player = $this->getPlayer();
        return match ($index) {
            "name" => new StringVariable($player->getName()),
            "display_name" => new StringVariable($player->getDisplayName()),
            "locale" => new StringVariable($player->getLocale()),
            "ping" => new NumberVariable($player->getNetworkSession()->getPing()),
            "ip" => new StringVariable($player->getNetworkSession()->getIp()),
            "port" => new NumberVariable($player->getNetworkSession()->getPort()),
            "uuid" => new StringVariable($player->getUniqueId()->toString()),
            "spawn_point" => new PositionObjectVariable($player->getSpawn()),
            "flying" => new BoolVariable($player->isFlying()),
            default => parent::getValueFromIndex($index),
        };
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    public function getPlayer(): Player {
        return $this->getEntity();
    }

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "name" => new DummyVariable(DummyVariable::STRING),
            "display_name" => new DummyVariable(DummyVariable::STRING),
            "locale" => new DummyVariable(DummyVariable::STRING),
            "ping" => new DummyVariable(DummyVariable::NUMBER),
            "ip" => new DummyVariable(DummyVariable::STRING),
            "port" => new DummyVariable(DummyVariable::NUMBER),
            "uuid" => new DummyVariable(DummyVariable::STRING),
            "spawn_point" => new DummyVariable(DummyVariable::POSITION),
            "flying" => new DummyVariable(DummyVariable::BOOLEAN),
        ]);
    }

    public function __toString(): string {
        $value = $this->getPlayer();
        return $value->getName();
    }
}