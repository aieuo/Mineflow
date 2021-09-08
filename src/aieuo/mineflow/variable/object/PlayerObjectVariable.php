<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\Player;

class PlayerObjectVariable extends HumanObjectVariable {

    public function __construct(Player $value, ?string $str = null) {
        parent::__construct($value, $str ?? $value->getName());
    }

    public function getProperty(string $name): ?Variable {
        $variable = parent::getProperty($name);
        if ($variable !== null) return $variable;

        $player = $this->getPlayer();
        return match ($name) {
            "name" => new StringVariable($player->getName()),
            "display_name" => new StringVariable($player->getDisplayName()),
            "locale" => new StringVariable($player->getLocale()),
            "ping" => new NumberVariable($player->getPing()),
            default => null,
        };
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    public function getPlayer(): Player {
        return $this->getValue();
    }

    public static function getTypeName(): string {
        return "player";
    }

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "name" => new DummyVariable(StringVariable::class),
        ]);
    }

    public function __toString(): string {
        $value = $this->getPlayer();
        return $value->getName();
    }
}