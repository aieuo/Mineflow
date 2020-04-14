<?php


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\recipe\Recipe;
use pocketmine\utils\Config;

interface ConfigFileFlowItem {

    public function getConfigVariableName(): string;

    public function setConfigVariableName(string $name);

    public function getConfig(Recipe $origin): ?Config;

    public function throwIfInvalidConfig(?Config $block);
}
