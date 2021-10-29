<?php


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\utils\Config;

interface ConfigFileFlowItem {

    public function getConfigVariableName(string $name = ""): string;

    public function setConfigVariableName(string $config, string $name = ""): void;

    /** @throws InvalidFlowValueException */
    public function getConfig(FlowItemExecutor $source, string $name = ""): Config;
}
