<?php
declare(strict_types=1);


namespace aieuo\mineflow\variable\registry;

use aieuo\mineflow\exception\UndefinedMineflowPropertyException;
use aieuo\mineflow\exception\UndefinedMineflowVariableException;
use aieuo\mineflow\variable\Variable;
use function array_shift;
use function explode;

class VariableRegistry {

    private static ?self $globalInstance = null;

    public static function global(): self {
        if (self::$globalInstance === null) {
            self::$globalInstance = new self();
        }

        return self::$globalInstance;
    }

    /**
     * @param array<string, Variable> $variables
     */
    public function __construct(private array $variables = []) {
    }

    public function add(string $name, Variable $variable): void {
        $this->variables[$name] = $variable;
    }

    public function get(string $name): ?Variable {
        return $this->variables[$name] ?? null;
    }

    public function mustGet(string $name): Variable {
        return $this->get($name) ?? throw new UndefinedMineflowVariableException($name);
    }

    public function getNested(string $name): ?Variable {
        $names = explode(".", $name);
        $name = array_shift($names);
        if (!$this->exists($name)) return null;

        $variable = $this->get($name);
        foreach ($names as $name1) {
            if (!($variable instanceof Variable)) return null;
            $variable = $variable->getProperty($name1);
        }
        return $variable;
    }

    public function mustGetNested(string $name): Variable {
        $names = explode(".", $name);
        $name = array_shift($names);

        $variable = $this->mustGet($name);

        $tmp = $name;
        foreach ($names as $name1) {
            $variable = $variable->getProperty($name1);

            if ($variable === null) {
                throw new UndefinedMineflowPropertyException($tmp, $name1);
            }
            $tmp .= ".".$name1;
        }

        return $variable;
    }

    public function remove(string $name): void {
        unset($this->variables[$name]);
    }

    public function exists(string $name): bool {
        return isset($this->variables[$name]);
    }

    /**
     * @return array<string, Variable>
     */
    public function getAll(): array {
        return $this->variables;
    }

}