<?php

declare(strict_types=1);


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\ConfigVariable;
use pocketmine\utils\Config;

trait ConfigFileFlowItemTrait {

    /* @var string[] */
    private array $configVariableNames = [];

    public function getConfigVariableName(string $name = ""): string {
        return $this->configVariableNames[$name] ?? "";
    }

    public function setConfigVariableName(string $config, string $name = ""): void {
        $this->configVariableNames[$name] = $config;
    }

    public function getConfig(FlowItemExecutor $source, string $name = ""): Config {
        $config = $source->replaceVariables($rawName = $this->getConfigVariableName($name));

        $variable = $source->getVariable($config);
        if ($variable instanceof ConfigVariable) {
            return $variable->getValue();
        }

        throw new InvalidFlowValueException($this->getName(), Language::get("action.target.not.valid", [["action.target.require.config"], $rawName]));
    }
}
