<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\utils\Scoreboard;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\MapVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\Variable;
use aieuo\mineflow\variable\VariableMethod;
use function array_map;

class ScoreboardVariable extends ObjectVariable {

    public static function getTypeName(): string {
        return "scoreboard";
    }

    public function __construct(private Scoreboard $scoreboard) {
    }

    public function getValue(): Scoreboard {
        return $this->scoreboard;
    }

    protected function getValueFromIndex(string $index): ?Variable {
        $board = $this->getValue();
        $scores = $board->getScores();

        if (!isset($scores[$index])) return null;
        return new NumberVariable($scores[$index]);
    }

    public static function registerProperties(string $class = self::class): void {
        self::registerMethod($class, "scores", new VariableMethod(
            new DummyVariable(MapVariable::class),
            fn(Scoreboard $scoreboard) => new MapVariable(array_map(fn(int $score) => new NumberVariable($score), $scoreboard->getScores())),
        ));
    }
}