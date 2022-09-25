<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\Main;
use aieuo\mineflow\variable\BooleanVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\MapVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\utils\Config;
use function array_keys;
use function array_reverse;
use function array_values;
use function count;
use function is_array;
use function is_bool;
use function is_numeric;

class ConfigVariable extends ObjectVariable {

    public static function getTypeName(): string {
        return "config";
    }

    public function __construct(private Config $config) {
    }

    public function getValueFromIndex(string $index): ?Variable {
        $config = $this->getValue();
        $data = $config->get($index);
        if ($data === null) return null;
        if (is_string($data)) return new StringVariable($data);
        if (is_numeric($data)) return new NumberVariable($data);
        if (!is_array($data)) return null;

        if (array_is_list($data)) {
            $variable = new ListVariable(Main::getVariableHelper()->toVariableArray($data));
        } else {
            $variable = new MapVariable(Main::getVariableHelper()->toVariableArray($data));
        }
        return $variable;
    }

    public function getValue(): Config {
        return $this->config;
    }

    public function map(string|array|Variable $target, ?FlowItemExecutor $executor = null, array $variables = [], bool $global = false): MapVariable {
        $variableHelper = Main::getVariableHelper();
        $values = [];
        foreach ($this->getValue()->getAll() as $key => $value) {
            $variable = match (true) {
                is_array($value) => $variableHelper->arrayToListVariable($value),
                is_numeric($value) => new NumberVariable($value),
                is_bool($value) => new BooleanVariable($value),
                default => new StringVariable($value),
            };
            $variables["it"] = $variable;
            $values[$key] = $variableHelper->runAST($target, $executor, $variables, $global);
        }
        return new MapVariable($values);
    }

    public function callMethod(string $name, array $parameters = []): ?Variable {
        $helper = Main::getVariableHelper();
        $values = $this->getConfig()->getAll();
        return match ($name) {
            "count" => new NumberVariable(count($values)),
            "reverse" => new MapVariable(array_reverse($values)),
            "keys" => $helper->arrayToListVariable(array_keys($values)),
            "values" => $helper->arrayToListVariable(array_values($values)),
            "all" => $helper->arrayToListVariable($values),
            default => null,
        };
    }

    public function __toString(): string {
        return "Config(".$this->getValue()->getPath().")";
    }
}
