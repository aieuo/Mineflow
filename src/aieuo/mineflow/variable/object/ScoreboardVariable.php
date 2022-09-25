<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\utils\Scoreboard;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\Variable;

class ScoreboardVariable extends ObjectVariable {

    public static function getTypeName(): string {
        return "scoreboard";
    }

    public function __construct(private Scoreboard $scoreboard) {
    }

    public function getValue(): Scoreboard {
        return $this->scoreboard;
    }

    public function getValueFromIndex(string $index): ?Variable {
        $board = $this->getValue();
        $scores = $board->getScores();

        if (!isset($scores[$index])) return null;
        return new NumberVariable($scores[$index]);
    }
}
