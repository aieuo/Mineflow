<?php


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\recipe\Recipe;
use pocketmine\block\Block;

interface BlockFlowItem {

    public function getBlockVariableName(): string;

    public function setBlockVariableName(string $name);

    public function getBlock(Recipe $origin): ?Block;

    public function throwIfInvalidBlock(?Block $block);
}
