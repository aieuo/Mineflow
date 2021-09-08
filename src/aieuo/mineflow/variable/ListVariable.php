<?php

namespace aieuo\mineflow\variable;

use aieuo\mineflow\exception\UnsupportedCalculationException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\Main;
use function array_values;

class ListVariable extends Variable implements \JsonSerializable {

    private ?string $showString;

    public static function getTypeName(): string {
        return "list";
    }

    /**
     * @param Variable[] $values
     * @param string|null $str
     */
    public function __construct(protected array $values, ?string $str = "") {
        $this->showString = $str;
    }

    public function getValue(): array {
        return $this->values;
    }

    public function appendValue(Variable $value): void {
        $this->values[] = $value;
    }

    public function setValueAt(int|string $key, Variable $value): void {
        $this->values[(int)$key] = $value;
        $this->values = array_values($this->values);
    }

    public function removeValue(Variable $value): void {
        $index = array_search($value, $this->values, true);
        if ($index === false) return;
        unset($this->values[$index]);
        $this->values = array_values($this->values);
    }

    public function removeValueAt(int|string $index): void {
        unset($this->values[(int)$index]);
        $this->values = array_values($this->values);
    }

    public function getValueFromIndex(string $index): ?Variable {
        return $this->values[(int)$index] ?? null;
    }

    public function add(Variable $target): ListVariable {
        if ($target instanceof ListVariable) throw new UnsupportedCalculationException();

        $values = [];
        foreach ($this->getValue() as $value) {
            $values[] = $value->add($target);
        }
        return new ListVariable($values);
    }

    public function sub(Variable $target): ListVariable {
        if ($target instanceof ListVariable) throw new UnsupportedCalculationException();

        $values = [];
        foreach ($this->getValue() as $value) {
            $values[] = $value->sub($target);
        }
        return new ListVariable($values);
    }

    public function mul(Variable $target): ListVariable {
        if ($target instanceof ListVariable) throw new UnsupportedCalculationException();

        $values = [];
        foreach ($this->getValue() as $value) {
            $values[] = $value->mul($target);
        }
        return new ListVariable($values);
    }

    public function div(Variable $target): ListVariable {
        if ($target instanceof ListVariable) throw new UnsupportedCalculationException();

        $values = [];
        foreach ($this->getValue() as $value) {
            $values[] = $value->div($target);
        }
        return new ListVariable($values);
    }

    public function map(string|array|Variable $target, ?FlowItemExecutor $executor = null, array $variables = [], bool $global = false): ListVariable {
        $variableHelper = Main::getVariableHelper();
        $values = [];
        foreach ($this->getValue() as $value) {
            $variables["it"] = $value;
            $values[] = $variableHelper->runAST($target, $executor, $variables, $global);
        }
        return new ListVariable($values);
    }

    public function callMethod(string $name, array $parameters = []): ?Variable {
        switch ($name) {
            case "count":
                return new NumberVariable(count($this->values));
        }
        return null;
    }

    public function getCount(): int {
        return count($this->values);
    }

    public function __toString(): string {
        if (!empty($this->getShowString())) return $this->getShowString();

        $values = [];
        foreach ($this->getValue() as $value) {
            $values[] = (string)$value;
        }
        return "[".implode(",", $values)."]";
    }

    public function getShowString(): string {
        return $this->showString;
    }

    public function jsonSerialize(): array {
        return [
            "type" => static::getTypeName(),
            "value" => $this->getValue(),
        ];
    }

    public function toArray(): array {
        $result = [];
        foreach ($this->getValue() as $i => $value) {
            if ($value instanceof ListVariable) $result[$i] = $value->toArray();
            else $result[$i] = (string)$value;
        }
        return $result;
    }
}