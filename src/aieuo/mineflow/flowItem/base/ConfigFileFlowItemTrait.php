<?php


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\ConfigObjectVariable;
use pocketmine\utils\Config;

trait ConfigFileFlowItemTrait {

    /* @var string[] */
    private $configVariableNames = [];

    public function getConfigVariableName(string $name = ""): string {
        return $this->configVariableNames[$name] ?? "";
    }

    public function setConfigVariableName(string $config, string $name = "") {
        $this->configVariableNames[$name] = $config;
        return $this;
    }

    public function getConfig(Recipe $origin, string $name = ""): ?Config {
        $config = $origin->replaceVariables($this->getConfigVariableName($name));

        $variable = $origin->getVariable($config);
        if (!($variable instanceof ConfigObjectVariable)) return null;
        return $variable->getConfig();
    }

    public function throwIfInvalidConfig(?Config $config) {
        if (!($config instanceof Config)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("flowItem.target.not.valid", [$this->getName(), ["flowItem.target.require.config"], $this->getConfigVariableName()]));
        }
    }
}