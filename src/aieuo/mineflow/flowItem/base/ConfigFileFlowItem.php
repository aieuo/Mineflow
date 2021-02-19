<?php


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\recipe\Recipe;
use pocketmine\utils\Config;

interface ConfigFileFlowItem {

    public function getConfigVariableName(string $name = ""): string;

    public function setConfigVariableName(string $config, string $name = ""): void;

    /**
     * @param Recipe $origin
     * @param string $name
     * @return Config
     * @throws InvalidFlowValueException
     */
    public function getConfig(Recipe $origin, string $name = ""): Config;

    /**
     * @param Config|null $config
     * @deprecated merge this into getConfig()
     */
    public function throwIfInvalidConfig(?Config $config): void;
}
