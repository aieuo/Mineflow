<?php


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\block\Block;

interface BlockFlowItem {

    public function getBlockVariableName(string $name = ""): string;

    public function setBlockVariableName(string $block, string $name = ""): void;

    /**
     * @param FlowItemExecutor $source
     * @param string $name
     * @return Block
     * @throws InvalidFlowValueException
     */
    public function getBlock(FlowItemExecutor $source, string $name = ""): Block;
}
