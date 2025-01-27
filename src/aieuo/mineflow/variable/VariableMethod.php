<?php
declare(strict_types=1);


namespace aieuo\mineflow\variable;

class VariableMethod {

    public function __construct(
        private DummyVariable $type,
        private \Closure      $closure,
        private bool          $passVariable = false,
    ) {
    }

    public function getType(): DummyVariable {
        return $this->type;
    }

    public function getClosure(): \Closure {
        return $this->closure;
    }

    public function call(Variable $variable, array $arguments): Variable {
        if ($this->passVariable) {
            return ($this->closure)($variable, ...$arguments);
        }

        return ($this->closure)($variable->getValue(), ...$arguments);
    }

}