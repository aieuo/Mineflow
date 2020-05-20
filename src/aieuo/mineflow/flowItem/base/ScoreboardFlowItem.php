<?php


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Scoreboard;

interface ScoreboardFlowItem {

    public function getScoreboardVariableName(): string;

    public function setScoreboardVariableName(string $name);

    public function getScoreboard(Recipe $origin): ?Scoreboard;

    public function throwIfInvalidScoreboard(?Scoreboard $board);
}