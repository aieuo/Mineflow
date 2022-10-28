<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\variable\BoolVariable;
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

        if (array_is_list($data)) {
            $variable = new ListVariable(Mineflow::getVariableHelper()->toVariableArray($data));
        } else {
            $variable = new MapVariable(Mineflow::getVariableHelper()->toVariableArray($data));
        }
        return $variable;
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    public function getConfig(): Config {
        return $this->getValue();
    }

    public function map(string|array|Variable $target, ?FlowItemExecutor $executor = null, array $variables = [], bool $global = false): MapVariable {
        $variableHelper = Mineflow::getVariableHelper();
        $values = [];
        foreach ($this->getConfig()->getAll() as $key => $value) {
            $variable = match (true) {
                is_array($value) => $variableHelper->arrayToListVariable($value),
                is_numeric($value) => new NumberVariable($value),
                is_bool($value) => new BoolVariable($value),
                default => new StringVariable($value),
            };
            $variables["it"] = $variable;
            $values[$key] = $variableHelper->runAST($target, $executor, $variables, $global);
        }
        return new MapVariable($values);
    }

    public function callMethod(string $name, array $parameters = []): ?Variable {
        $helper = Mineflow::getVariableHelper();
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
        return "Config(".$this->getConfig()->getPath().")";
    }
}
