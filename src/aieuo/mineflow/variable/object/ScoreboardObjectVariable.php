<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\utils\Scoreboard;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\Variable;

class ScoreboardObjectVariable extends ObjectVariable {

    public function getProperty(string $name): ?Variable {
        $board = $this->getScoreboard();
        $scores = $board->getScores();

        if (!isset($scores[$name])) return null;
        return new NumberVariable($scores[$name]);
    }

    public static function getTypeName(): string {
        return "scoreboard";
    }

    public static function getValuesDummy(): array {
        return [];
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    public function getScoreboard(): Scoreboard {
        return $this->getValue();
    }
}