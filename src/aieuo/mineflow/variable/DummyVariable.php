<?php

namespace aieuo\mineflow\variable;

use aieuo\mineflow\variable\object\BlockObjectVariable;
use aieuo\mineflow\variable\object\ConfigObjectVariable;
use aieuo\mineflow\variable\object\EntityObjectVariable;
use aieuo\mineflow\variable\object\EventObjectVariable;
use aieuo\mineflow\variable\object\HumanObjectVariable;
use aieuo\mineflow\variable\object\ItemObjectVariable;
use aieuo\mineflow\variable\object\LevelObjectVariable;
use aieuo\mineflow\variable\object\LocationObjectVariable;
use aieuo\mineflow\variable\object\PlayerObjectVariable;
use aieuo\mineflow\variable\object\PositionObjectVariable;
use aieuo\mineflow\variable\object\ScoreboardObjectVariable;

class DummyVariable extends Variable {

    public $type = Variable::DUMMY;

    /** @var string  */
    protected $name;
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
        $this->name = $name;
        $this->valueType = $valueType;
        $this->description = $description;
        parent::__construct("");
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function getValueType(): string {
        return $this->valueType;
    }

    public function toStringVariable(): StringVariable {
        return new StringVariable($this->getValue());
    }

    /**
     * @return DummyVariable[]
     */
    public function getObjectValuesDummy(): array {
        switch ($this->getValueType()) {
            case self::BLOCK:
                return BlockObjectVariable::getValuesDummy($this->getName());
            case self::CONFIG:
                return ConfigObjectVariable::getValuesDummy($this->getName());
            case self::ENTITY:
                return EntityObjectVariable::getValuesDummy($this->getName());
            case self::EVENT:
                return EventObjectVariable::getValuesDummy($this->getName());
            case self::HUMAN:
                return HumanObjectVariable::getValuesDummy($this->getName());
            case self::ITEM:
                return ItemObjectVariable::getValuesDummy($this->getName());
            case self::LEVEL:
                return LevelObjectVariable::getValuesDummy($this->getName());
            case self::LOCATION:
                return LocationObjectVariable::getValuesDummy($this->getName());
            case self::PLAYER:
                return PlayerObjectVariable::getValuesDummy($this->getName());
            case self::POSITION:
                return PositionObjectVariable::getValuesDummy($this->getName());
            case self::SCOREBOARD:
                return ScoreboardObjectVariable::getValuesDummy($this->getName());
            default:
                return [];
        }
    }

    public function isObjectVariableType(): bool {
        return in_array($this->getValueType(), [
            self::BLOCK,
            self::CONFIG,
            self::ENTITY,
            self::EVENT,
            self::HUMAN,
            self::ITEM,
            self::LEVEL,
            self::LOCATION,
            self::PLAYER,
            self::POSITION,
            self::SCOREBOARD,
        ], true);
    }
}