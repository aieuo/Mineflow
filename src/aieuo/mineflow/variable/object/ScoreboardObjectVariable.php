<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\utils\Scoreboard;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\Variable;

class ScoreboardObjectVariable extends ObjectVariable {

    public function getValueFromIndex(string $index): ?Variable {
        $board = $this->getScoreboard();
        $scores = $board->getScores();

        if (!isset($scores[$index])) return null;
        return new NumberVariable($scores[$index]);
    }

    public function getScoreboard(): Scoreboard {
        /** @var Scoreboard $value */
        $value = $this->getValue();
        return $value;
    }
}