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

    public function call(Variable $variable, array $arguments): void {
        if ($this->passVariable) {
            ($this->closure)($variable, ...$arguments);
        } else {
            ($this->closure)($variable->getValue(), ...$arguments);
        }
    }

}
