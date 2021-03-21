<?php


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Scoreboard;
use aieuo\mineflow\variable\object\ScoreboardObjectVariable;

trait ScoreboardFlowItemTrait {

    /* @var string[] */
    private $scoreboardVariableNames = [];

    public function getScoreboardVariableName(string $name = ""): string {
        return $this->scoreboardVariableNames[$name] ?? "";
    }

    public function setScoreboardVariableName(string $scoreboard, string $name = ""): void {
        $this->scoreboardVariableNames[$name] = $scoreboard;
    }

    public function getScoreboard(Recipe $source, string $name = ""): Scoreboard {
        $scoreboard = $source->replaceVariables($rawName = $this->getScoreboardVariableName($name));

        $variable = $source->getVariable($scoreboard);
        if ($variable instanceof ScoreboardObjectVariable and ($scoreboard = $variable->getScoreboard()) instanceof Scoreboard) {
            return $scoreboard;
        }

        throw new InvalidFlowValueException($this->getName(), Language::get("action.target.not.valid", [["action.target.require.scoreboard"], $rawName]));
    }
}