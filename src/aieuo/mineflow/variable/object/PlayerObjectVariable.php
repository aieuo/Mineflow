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

    public function getValueFromIndex(string $index): ?Variable {
        $variable = parent::getValueFromIndex($index);
        if ($variable !== null) return $variable;

        $player = $this->getPlayer();
        switch ($index) {
            case "name":
                return new StringVariable($player->getName());
            case "display_name":
                return new StringVariable($player->getDisplayName());
            case "locale":
                return new StringVariable($player->getLocale());
            case "ping":
                return new NumberVariable($player->getPing());
            default:
                return null;
        }
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    public function getPlayer(): Player {
        return $this->getValue();
    }

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "name" => new DummyVariable(DummyVariable::STRING),
        ]);
    }

    public function __toString(): string {
        $value = $this->getPlayer();
        return $value->getName();
    }
}