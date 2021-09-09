<?php
declare(strict_types=1);

namespace aieuo\mineflow\trigger;

use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\Variable;

abstract class Trigger implements \JsonSerializable {

    private string $type;

    private string $key;

    private string $subKey;

    abstract public static function create(string $key, string $subKey = ""): Trigger;

    public function __construct(string $type, string $key, string $subKey = "") {
        $this->type = $type;
        $this->key = $key;
        $this->subKey = $subKey;
    }

    public function getType(): string {
        return $this->type;
    }

    public function getKey(): string {
        return $this->key;
    }

    public function setKey(string $key): void {
        $this->key = $key;
    }

    public function getSubKey(): string {
        return $this->subKey;
    }

    public function setSubKey(string $subKey): void {
        $this->subKey = $subKey;
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

    public function jsonSerialize(): array {
        return [
            "type" => $this->getType(),
            "key" => $this->getKey(),
            "subKey" => $this->getSubKey(),
        ];
    }

    public function __toString(): string {
        $translate = "trigger.type.".$this->getType();
        return (Language::exists($translate) ? Language::get($translate) : $this->getType()).": ".$this->getKey().", ".$this->getSubKey();
    }
}