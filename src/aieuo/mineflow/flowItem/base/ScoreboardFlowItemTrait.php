<?php


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Scoreboard;
use aieuo\mineflow\variable\object\ScoreboardObjectVariable;

trait ScoreboardFlowItemTrait {

    /* @var string */
    private $scoreboardVariableName = "board";

    public function getScoreboardVariableName(): string {
        return $this->scoreboardVariableName;
    }

    public function setScoreboardVariableName(string $name) {
        $this->scoreboardVariableName = $name;
        return $this;
    }

    public function getScoreboard(Recipe $origin): ?Scoreboard {
        $name = $origin->replaceVariables($this->getScoreboardVariableName());

        $variable = $origin->getVariable($name);
        if (!($variable instanceof ScoreboardObjectVariable)) return null;
        return $variable->getScoreboard();
    }

    public function throwIfInvalidScoreboard(?Scoreboard $board) {
        if (!($board instanceof Scoreboard)) {
            throw new \UnexpectedValueException(Language::get("flowItem.target.not.valid", [$this->getName(), ["flowItem.target.require.scoreboard"], $this->getScoreboardVariableName()]));
        }
    }
}