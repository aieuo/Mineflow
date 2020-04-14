<?php


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\ConfigObjectVariable;
use pocketmine\utils\Config;

trait ConfigFileFlowItemTrait {

    /* @var string */
    private $configVariableName = "config";

    public function getConfigVariableName(): string {
        return $this->configVariableName;
    }

    public function setConfigVariableName(string $name) {
        $this->configVariableName = $name;
        return $this;
    }

    public function getConfig(Recipe $origin): ?Config {
        $name = $origin->replaceVariables($this->getConfigVariableName());

        $variable = $origin->getVariable($name);
        if (!($variable instanceof ConfigObjectVariable)) return null;
        return $variable->getConfig();
    }

    public function throwIfInvalidConfig(?Config $config) {
        if (!($config instanceof Config)) {
            throw new \UnexpectedValueException(Language::get("flowItem.target.not.valid", [$this->getName(), ["flowItem.target.require.config"]]));
        }
    }
}