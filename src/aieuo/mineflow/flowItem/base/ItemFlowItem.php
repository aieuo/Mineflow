<?php


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\recipe\Recipe;
use pocketmine\item\Item;

interface ItemFlowItem {

    public function getItemVariableName(string $name = ""): string;

    public function setItemVariableName(string $item, string $name = "");

    public function getItem(Recipe $origin, string $name = ""): ?Item;

    public function throwIfInvalidItem(?Item $item);
}