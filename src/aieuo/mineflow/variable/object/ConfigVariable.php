<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\Main;
use aieuo\mineflow\variable\BooleanVariable;
use aieuo\mineflow\variable\DummyVariable;
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

    protected function getValueFromIndex(string $index): ?Variable {
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

    public function __toString(): string {
        return "Config(".$this->getValue()->getPath().")";
    }

    public static function registerProperties(string $class = self::class): void {
        self::registerMethod(
            $class, "count", new DummyVariable(NumberVariable::class),
            fn(Config $config) => new NumberVariable(count($config->getAll())),
        );
        self::registerMethod(
            $class, "reverse", new DummyVariable(MapVariable::class),
            fn(Config $config) => new MapVariable(array_reverse($config->getAll())),
            aliases: ["reversed"],
        );
        self::registerMethod(
            $class, "keys", new DummyVariable(ListVariable::class),
            fn(Config $config) => Main::getVariableHelper()->arrayToListVariable(array_keys($config->getAll())),
        );
        self::registerMethod(
            $class, "values", new DummyVariable(ListVariable::class),
            fn(Config $config) => Main::getVariableHelper()->arrayToListVariable(array_values($config->getAll())),
        );
        self::registerMethod(
            $class, "all", new DummyVariable(ListVariable::class),
            fn(Config $config) => Main::getVariableHelper()->arrayToListVariable($config->getAll()),
        );
    }
}
