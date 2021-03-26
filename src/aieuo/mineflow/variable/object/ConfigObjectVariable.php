<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\Main;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\MapVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\utils\Config;

class ConfigObjectVariable extends ObjectVariable {

    public function __construct(Config $value, ?string $str = null) {
        parent::__construct($value, $str);
    }

    public function getValueFromIndex(string $index): ?Variable {
        $config = $this->getConfig();
        $data = $config->get($index);
        if ($data === null) return null;
        if (is_string($data)) return new StringVariable($data);
        if (is_numeric($data)) return new NumberVariable($data);
        if (!is_array($data)) return null;

        if (array_values($data) === $data) {
            $variable = new ListVariable(Main::getVariableHelper()->toVariableArray($data));
        } else {
            $variable = new MapVariable(Main::getVariableHelper()->toVariableArray($data));
        }
        return $variable;
    }

    public function getConfig(): Config {
        /** @var Config $value */
        $value = $this->getValue();
        return $value;
    }

    public function __toString(): string {
        return "Config(".$this->getConfig()->getPath().")";
    }
}