<?php

namespace aieuo\mineflow\trigger;

use pocketmine\entity\Entity;

class Trigger implements \JsonSerializable {

    const TYPE_BLOCK = "block";
    const TYPE_COMMAND = "command";
    const TYPE_EVENT = "event";
    const TYPE_FORM = "form";

    /** @var string */
    private $type;

    /** @var string */
    private $key;

    public function __construct(string $type, string $key) {
        $this->type = $type;
        $this->key = $key;
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

    public function jsonSerialize() {
        return [
            "type" => $this->getType(),
            "key" => $this->getKey(),
        ];
    }
}