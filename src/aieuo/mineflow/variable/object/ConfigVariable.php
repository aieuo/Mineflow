<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\Mineflow;
use aieuo\mineflow\variable\BooleanVariable;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\IteratorVariable;
use aieuo\mineflow\variable\IteratorVariableTrait;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\MapVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use aieuo\mineflow\variable\VariableMethod;
use pocketmine\utils\Config;
use function array_reverse;
use function is_array;
use function is_bool;
use function is_numeric;

class ConfigVariable extends ObjectVariable implements IteratorVariable {
    use IteratorVariableTrait;

    public static function getTypeName(): string {
        return "config";
    }

    public function __construct(private Config $config) {
    }

    protected function getValueFromIndex(string $index): ?Variable {
        $config = $this->getValue();
        $data = $config->get($index);
        if ($data === null) return $this->pluck($index);
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

    public function getValue(): Config {
        return $this->config;
    }

    public function getIterator(): \Traversable {
        $variableHelper = Mineflow::getVariableHelper();
        foreach ($this->getValue()->getAll() as $key => $value) {
            $variable = match (true) {
                is_array($value) => $variableHelper->arrayToListVariable($value),
                is_numeric($value) => new NumberVariable($value),
                is_bool($value) => new BooleanVariable($value),
                default => new StringVariable($value),
            };
            yield $key => $variable;
        }
    }

    public function hasKey(int|string $key): bool {
        return $this->getValue()->exists($key);
    }

    public function setValueAt(int|string $key, Variable $value): void {
        $this->getValue()->set($key, match (true) {
            $value instanceof IteratorVariable => $value->toArray(),
            $value instanceof NumberVariable => $value->getValue(),
            default => (string)$value,
        });
    }

    public function removeValueAt(int|string $index): void {
        $this->getValue()->remove($index);
    }

    public function __toString(): string {
        return "Config(".$this->getValue()->getPath().")";
    }

    public static function registerProperties(string $class = self::class): void {
        self::registerMethod($class, "reverse", new VariableMethod(
            new DummyVariable(MapVariable::class),
            fn(Config $config) => new MapVariable(array_reverse($config->getAll())),
        ), aliases: ["reversed"]);
        self::registerMethod($class, "all", new VariableMethod(
            new DummyVariable(ListVariable::class),
            fn(Config $config) => Mineflow::getVariableHelper()->arrayToListVariable($config->getAll()),
        ));

        self::registerIteratorMethods($class);
    }
}