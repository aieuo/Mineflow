<?php


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\recipe\Recipe;
use pocketmine\block\Block;

interface BlockFlowItem {

    public function getBlockVariableName(string $name = ""): string;

    public function setBlockVariableName(string $block, string $name = ""): void;

    /**
     * @param Recipe $origin
     * @param string $name
     * @return Block
     * @throws InvalidFlowValueException
     */
    public function getBlock(Recipe $origin, string $name = ""): Block;

    /**
     * @param Block|null $block
     * @deprecated merge this into getBlock()
     */
    public function throwIfInvalidBlock(?Block $block): void;
}
