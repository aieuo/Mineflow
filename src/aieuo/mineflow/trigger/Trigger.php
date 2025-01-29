<?php
declare(strict_types=1);

namespace aieuo\mineflow\trigger;

use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\Variable;

abstract class Trigger {

    public function __construct(private readonly string $type) {
    }

    public function getType(): string {
        return $this->type;
    }

    /**
     * @param mixed $data
     * @return array<string, Variable>
     */
    public function getVariables(mixed $data): array {
        return []; // ["variable name" => $variable]
    }

    /**
     * @return array<string, DummyVariable>
     */
    public function getVariablesDummy(): array {
        return []; // ["variable name" => $variable];
    }

    abstract public function hash(): string|int;

    public function equals(Trigger $trigger): bool {
        return $trigger->getType() === $this->getType() and $trigger->hash() === $this->hash();
    }

    public function __toString(): string {
        $translate = "trigger.type.".$this->getType();
        return (Language::exists($translate) ? Language::get($translate) : $this->getType()).": ".$this->hash();
    }
}