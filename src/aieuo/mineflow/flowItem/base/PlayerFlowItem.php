<?php


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\recipe\Recipe;
use pocketmine\Player;

interface PlayerFlowItem {

    public function getPlayerVariableName(string $name = ""): string;

    public function setPlayerVariableName(string $player, string $name = "");

    public function getPlayer(Recipe $origin, string $name): ?Player;

    public function throwIfInvalidPlayer(?Player $player);
}