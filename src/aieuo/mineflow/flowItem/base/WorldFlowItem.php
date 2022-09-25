<?php

declare(strict_types=1);


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\world\World;

interface WorldFlowItem {

    public function getWorldVariableName(string $name = ""): string;

    public function setWorldVariableName(string $position, string $name = ""): void;

    /** @throws InvalidFlowValueException */
    public function getWorld(FlowItemExecutor $source, string $name = ""): World;
}
