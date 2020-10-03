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

    public function getScoreboard(Recipe $origin, string $name = ""): ?Scoreboard {
        $scoreboard = $origin->replaceVariables($this->getScoreboardVariableName($name));

        $variable = $origin->getVariable($scoreboard);
        if (!($variable instanceof ScoreboardObjectVariable)) return null;
        return $variable->getScoreboard();
    }

    public function throwIfInvalidScoreboard(?Scoreboard $board): void {
        if (!($board instanceof Scoreboard)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("flowItem.target.not.valid", [$this->getName(), ["flowItem.target.require.scoreboard"], $this->getScoreboardVariableName()]));
        }
    }
}