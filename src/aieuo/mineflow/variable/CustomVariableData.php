<?php
declare(strict_types=1);


namespace aieuo\mineflow\variable;

use aieuo\mineflow\variable\object\UnknownVariable;
use function array_key_first;
use function count;

class CustomVariableData {
    /**
     * @param array<Variable> $values
     * @param Variable|null $default
     */
    public function __construct(private array $values, private ?Variable $default = null) {
    }

    public function getValues(): array {
        return $this->values;
    }

    public function setValues(array $values): void {
        $this->values = $values;
    }

    public function getDefault(): ?Variable {
        return $this->default;
    }

    public function setDefault(?Variable $default): void {
        $this->default = $default;
    }

    public function getData(string $key): ?Variable {
        return $this->values[$key] ?? $this->default;
    }

    public function setData(string $key, Variable $data): void {
       $this->values[$key] = $data;
    }

    public function removeData(string $key): void {
       unset($this->values[$key]);
    }

    public function getType(): string {
        $types = [];
        foreach ($this->values as $value) {
            $types[$value::getTypeName()] = true;
        }
        if (count($types) === 1) {
            return array_key_first($types);
        }

        return UnknownVariable::getTypeName();
    }
}