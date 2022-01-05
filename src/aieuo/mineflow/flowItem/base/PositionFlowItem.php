<?php


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\world\Position;

interface PositionFlowItem {

    public function getPositionVariableName(string $name = ""): string;

    public function setPositionVariableName(string $position, string $name = ""): void;

    /**
     * @param FlowItemExecutor $source
     * @param string $name
     * @return Position
     * @throws InvalidFlowValueException
     */
    public function getPosition(FlowItemExecutor $source, string $name = ""): Position;
}