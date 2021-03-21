<?php


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\recipe\Recipe;
use pocketmine\block\Block;

interface BlockFlowItem {

    public function getBlockVariableName(string $name = ""): string;

    public function setBlockVariableName(string $block, string $name = ""): void;

    /**
     * @param Recipe $source
     * @param string $name
     * @return Block
     * @throws InvalidFlowValueException
     */
    public function getBlock(Recipe $source, string $name = ""): Block;
}
