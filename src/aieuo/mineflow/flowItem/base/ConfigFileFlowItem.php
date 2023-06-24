<?php

declare(strict_types=1);


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\argument\ConfigArgument;
use JetBrains\PhpStorm\Deprecated;
use pocketmine\utils\Config;

#[Deprecated]
/**
 * @see ConfigArgument
 */
interface ConfigFileFlowItem {

    public function getConfigVariableName(string $name = ""): string;

    public function setConfigVariableName(string $config, string $name = ""): void;

    /** @throws InvalidFlowValueException */
    public function getConfig(FlowItemExecutor $source, string $name = ""): Config;
}
