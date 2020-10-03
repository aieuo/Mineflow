<?php

namespace aieuo\mineflow\variable;

class DummyVariable extends Variable {

    public $type = Variable::DUMMY;

    /* @var string */
    private $description;
    /* @var string */
    private $valueType;

    public const UNKNOWN = "unknown";
    public const STRING = "string";
    public const NUMBER = "number";
    public const LIST = "list";
    public const MAP = "map";
    public const BLOCK = "block";
    public const CONFIG = "config";
    public const ENTITY = "entity";
    public const EVENT = "event";
    public const HUMAN = "human";
    public const ITEM = "item";
    public const LEVEL = "level";
    public const LOCATION = "location";
    public const PLAYER = "player";
    public const POSITION = "position";
    public const SCOREBOARD = "scoreboard";

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