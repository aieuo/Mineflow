<?php

namespace aieuo\mineflow\variable;

use aieuo\mineflow\variable\object\BlockObjectVariable;
use aieuo\mineflow\variable\object\ConfigObjectVariable;
use aieuo\mineflow\variable\object\EntityObjectVariable;
use aieuo\mineflow\variable\object\EventObjectVariable;
use aieuo\mineflow\variable\object\HumanObjectVariable;
use aieuo\mineflow\variable\object\InventoryObjectVariable;
use aieuo\mineflow\variable\object\ItemObjectVariable;
use aieuo\mineflow\variable\object\WorldObjectVariable;
use aieuo\mineflow\variable\object\LocationObjectVariable;
use aieuo\mineflow\variable\object\PlayerObjectVariable;
use aieuo\mineflow\variable\object\PositionObjectVariable;
use aieuo\mineflow\variable\object\ScoreboardObjectVariable;
use aieuo\mineflow\variable\object\Vector3ObjectVariable;

class DummyVariable extends Variable {

    public $type = Variable::DUMMY;

    /* @var string */
    private $description;
    /* @var string */
    private $valueType;

    public const UNKNOWN = "unknown";
    public const STRING = "string";
    public const NUMBER = "number";
    public const BOOLEAN = "boolean";
    public const LIST = "list";
    public const MAP = "map";
    public const BLOCK = "block";
    public const CONFIG = "config";
    public const ENTITY = "entity";
    public const EVENT = "event";
    public const HUMAN = "human";
    public const ITEM = "item";
    public const WORLD = "world";
    public const LOCATION = "location";
    public const PLAYER = "player";
    public const POSITION = "position";
    public const VECTOR3 = "vector3";
    public const SCOREBOARD = "scoreboard";
    public const INVENTORY = "inventory";

    public function __construct(string $valueType = "", string $description = "") {
        $this->valueType = $valueType;
        $this->description = $description;
        parent::__construct("");
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

    public function getValueFromIndex(string $index): ?Variable {
        $variables = $this->getObjectValuesDummy();
        return $variables[$index] ?? null;
    }

    /**
     * @return array<string, DummyVariable>
     */
    public function getObjectValuesDummy(): array {
        switch ($this->getValueType()) {
            case self::BLOCK:
                return BlockObjectVariable::getValuesDummy();
            case self::CONFIG:
                return ConfigObjectVariable::getValuesDummy();
            case self::ENTITY:
                return EntityObjectVariable::getValuesDummy();
            case self::EVENT:
                return EventObjectVariable::getValuesDummy();
            case self::HUMAN:
                return HumanObjectVariable::getValuesDummy();
            case self::ITEM:
                return ItemObjectVariable::getValuesDummy();
            case self::WORLD:
                return WorldObjectVariable::getValuesDummy();
            case self::LOCATION:
                return LocationObjectVariable::getValuesDummy();
            case self::PLAYER:
                return PlayerObjectVariable::getValuesDummy();
            case self::POSITION:
                return PositionObjectVariable::getValuesDummy();
            case self::VECTOR3:
                return Vector3ObjectVariable::getValuesDummy();
            case self::SCOREBOARD:
                return ScoreboardObjectVariable::getValuesDummy();
            case self::INVENTORY:
                return InventoryObjectVariable::getValuesDummy();
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
            self::WORLD,
            self::LOCATION,
            self::PLAYER,
            self::POSITION,
            self::VECTOR3,
            self::SCOREBOARD,
            self::INVENTORY,
        ], true);
    }
}