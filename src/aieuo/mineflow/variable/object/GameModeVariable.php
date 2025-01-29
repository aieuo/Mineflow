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
}