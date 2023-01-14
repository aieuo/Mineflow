<?php

declare(strict_types=1);


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\world\Position;

interface PositionFlowItem {

    public function getPositionVariableNames(): array;

    public function getPositionVariableName(string $name = ""): string;

    public function setPositionVariableName(string $position, string $name = ""): void;

    /** @throws InvalidFlowValueException */
    public function getPosition(FlowItemExecutor $source, string $name = ""): Position;
}
