<?php


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\recipe\Recipe;
use pocketmine\item\Item;

interface ItemFlowItem {

    public function getItemVariableName(): String;

    public function setItemVariableName(string $name);

    public function getItem(Recipe $origin): ?Item;

    public function throwIfInvalidItem(?Item $player);
}