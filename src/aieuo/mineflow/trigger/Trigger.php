<?php
declare(strict_types=1);

namespace aieuo\mineflow\trigger;

class Trigger implements \JsonSerializable {

    public const TYPE_BLOCK = "block";
    public const TYPE_COMMAND = "command";
    public const TYPE_EVENT = "event";
    public const TYPE_FORM = "form";

    /** @var string */
    private $type;

    /** @var string */
    private $key;

    /** @var string */
    private $subKey;

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

    public function jsonSerialize() {
        return [
            "type" => $this->getType(),
            "key" => $this->getKey(),
            "subKey" => $this->getSubKey(),
        ];
    }
}