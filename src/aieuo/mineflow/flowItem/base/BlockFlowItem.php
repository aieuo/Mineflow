<?php


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\recipe\Recipe;
use pocketmine\block\Block;

interface BlockFlowItem {

    public function getBlockVariableName(string $name = ""): string;

    public function setBlockVariableName(string $block, string $name = "");

    public function getBlock(Recipe $origin, string $name = ""): ?Block;

    public function throwIfInvalidBlock(?Block $block);
}
