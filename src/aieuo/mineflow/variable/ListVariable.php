<?php

namespace aieuo\mineflow\variable;

use aieuo\mineflow\exception\UnsupportedCalculationException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\Main;

class ListVariable extends Variable implements \JsonSerializable {

    public int $type = Variable::LIST;

    private ?string $showString;

    protected $value = [];

    /**
     * @return Variable[]
     */
    public function getValue(): array {
        return parent::getValue();
    }

    /**
     * @param Variable[] $value
     * @param string|null $str
     */
    public function __construct(array $value, ?string $str = "") {
        parent::__construct($value);
        $this->showString = $str;
    }

    public function appendValue(Variable $value): void {
        $this->value[] = $value;
    }

    public function setValueAt(int|string $key, Variable $value): void {
        $this->value[(int)$key] = $value;
        $this->value = array_values($this->value);
    }

    public function removeValue(Variable $value): void {
        $index = array_search($value, $this->value, true);
        if ($index === false) return;
        unset($this->value[$index]);
        $this->value = array_merge($this->value);
    }

    public function getValueFromIndex(string $index): ?Variable {
        if (!isset($this->value[(int)$index])) return null;
        return $this->value[(int)$index];
    }

    public function add($target): ListVariable {
        if ($target instanceof ListVariable) throw new UnsupportedCalculationException();

        $values = [];
        foreach ($this->getValue() as $value) {
            $values[] = $value->add($target);
        }
        return new ListVariable($values);
    }

    public function sub($target): ListVariable {
        if ($target instanceof ListVariable) throw new UnsupportedCalculationException();

        $values = [];
        foreach ($this->getValue() as $value) {
            $values[] = $value->sub($target);
        }
        return new ListVariable($values);
    }

    public function mul($target): ListVariable {
        if ($target instanceof ListVariable) throw new UnsupportedCalculationException();

        $values = [];
        foreach ($this->getValue() as $value) {
            $values[] = $value->mul($target);
        }
        return new ListVariable($values);
    }

    public function div($target): ListVariable {
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
        return match ($name) {
            "count" => new NumberVariable(count($this->value)),
            default => null,
        };
    }

    public function getCount(): int {
        return count($this->value);
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
            "type" => $this->getType(),
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