<?php

namespace aieuo\mineflow\variable;

class DummyVariable extends Variable {

    public $type = Variable::DUMMY;

    /* @var string */
    private $description;
    /* @var string */
    private $valueType;

    const UNKNOWN = "unknown";
    const STRING = "string";
    const NUMBER = "number";
    const LIST = "list";
    const MAP = "map";
    const BLOCK = "block";
    const CONFIG = "config";
    const ENTITY = "entity";
    const EVENT = "event";
    const HUMAN = "human";
    const ITEM = "item";
    const LEVEL = "level";
    const LOCATION = "location";
    const PLAYER = "player";
    const POSITION = "position";
    const SCOREBOARD = "scoreboard";

    public function __construct(string $name = "", string $valueType = "", string $description = "") {
        $this->valueType = $valueType;
        $this->description = $description;
        parent::__construct("", $name);
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function getValueType(): string {
        return $this->valueType;
    }

    public function toStringVariable(): StringVariable {
        return new StringVariable($this->getName(), $this->getValue());
    }

    public function isSavable(): bool {
        return false;
    }

    public static function fromArray(array $data): ?Variable {
        if (!isset($data["value"])) return null;
        return new self($data["value"], $data["name"] ?? "");
    }
}