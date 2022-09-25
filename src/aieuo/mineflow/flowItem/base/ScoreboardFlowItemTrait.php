<?php

declare(strict_types=1);


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Scoreboard;
use aieuo\mineflow\variable\object\ScoreboardVariable;

trait ScoreboardFlowItemTrait {

    /* @var string[] */
    private array $scoreboardVariableNames = [];

    public function getScoreboardVariableName(string $name = ""): string {
        return $this->scoreboardVariableNames[$name] ?? "";
    }

    public function setScoreboardVariableName(string $scoreboard, string $name = ""): void {
        $this->scoreboardVariableNames[$name] = $scoreboard;
    }

    public function getScoreboard(FlowItemExecutor $source, string $name = ""): Scoreboard {
        $scoreboard = $source->replaceVariables($rawName = $this->getScoreboardVariableName($name));

        $variable = $source->getVariable($scoreboard);
        if ($variable instanceof ScoreboardVariable) {
            return $variable->getValue();
        }

        throw new InvalidFlowValueException($this->getName(), Language::get("action.target.not.valid", [["action.target.require.scoreboard"], $rawName]));
    }
}
