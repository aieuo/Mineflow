<?php


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\world\Position;
use pocketmine\world\World;

interface WorldFlowItem {

    public function getWorldVariableName(string $name = ""): string;

    public function setWorldVariableName(string $position, string $name = ""): void;

    /**
     * @param FlowItemExecutor $source
     * @param string $name
     * @return World
     * @throws InvalidFlowValueException
     */
    public function getWorld(FlowItemExecutor $source, string $name = ""): World;
}