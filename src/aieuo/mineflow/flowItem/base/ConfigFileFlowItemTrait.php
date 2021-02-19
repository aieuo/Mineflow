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

    public function setConfigVariableName(string $config, string $name = ""): void {
        $this->configVariableNames[$name] = $config;
    }

    public function getConfig(Recipe $origin, string $name = ""): Config {
        $config = $origin->replaceVariables($rawName = $this->getConfigVariableName($name));

        $variable = $origin->getVariable($config);
        if ($variable instanceof ConfigObjectVariable and ($config = $variable->getConfig()) instanceof Config) {
            return $config;
        }

        throw new InvalidFlowValueException($this->getName(), Language::get("action.target.not.valid", [["action.target.require.config"], $rawName]));
    }

    /**
     * @param Config|null $config
     * @deprecated merge this into getConfig()
     */
    public function throwIfInvalidConfig(?Config $config): void {
        if (!($config instanceof Config)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.target.not.valid", [["action.target.require.config"], $this->getConfigVariableName()]));
        }
    }
}