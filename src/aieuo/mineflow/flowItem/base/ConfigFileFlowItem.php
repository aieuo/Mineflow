<?php


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\recipe\Recipe;
use pocketmine\utils\Config;

interface ConfigFileFlowItem {

    public function getConfigVariableName(string $name = ""): string;

    public function setConfigVariableName(string $config, string $name = ""): void;

    public function getConfig(Recipe $origin, string $name = ""): ?Config;

    public function throwIfInvalidConfig(?Config $block): void;
}
