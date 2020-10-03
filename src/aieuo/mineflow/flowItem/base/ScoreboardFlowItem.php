<?php


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Scoreboard;

interface ScoreboardFlowItem {

    public function getScoreboardVariableName(string $name = ""): string;

    public function setScoreboardVariableName(string $scoreboard, string $name = ""): void;

    public function getScoreboard(Recipe $origin, string $name = ""): ?Scoreboard;

    public function throwIfInvalidScoreboard(?Scoreboard $board): void;
}