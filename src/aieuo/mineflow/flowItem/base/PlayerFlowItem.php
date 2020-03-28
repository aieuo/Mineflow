<?php


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\recipe\Recipe;
use pocketmine\Player;

interface PlayerFlowItem {

    public function getPlayerVariableName(): string;

    public function setPlayerVariableName(string $name);

    public function getPlayer(Recipe $origin): ?Player;

    public function throwIfInvalidPlayer(?Player $player);
}