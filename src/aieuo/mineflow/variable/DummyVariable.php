<?php

namespace aieuo\mineflow\variable;

use aieuo\mineflow\variable\object\AxisAlignedBBVariable;
use aieuo\mineflow\variable\object\BlockVariable;
use aieuo\mineflow\variable\object\ConfigVariable;
use aieuo\mineflow\variable\object\EntityVariable;
use aieuo\mineflow\variable\object\EventVariable;
use aieuo\mineflow\variable\object\HumanVariable;
use aieuo\mineflow\variable\object\InventoryVariable;
use aieuo\mineflow\variable\object\ItemVariable;
use aieuo\mineflow\variable\object\LivingVariable;
use aieuo\mineflow\variable\object\RecipeVariable;
use aieuo\mineflow\variable\object\WorldVariable;
use aieuo\mineflow\variable\object\LocationVariable;
use aieuo\mineflow\variable\object\PlayerVariable;
use aieuo\mineflow\variable\object\PositionVariable;
use aieuo\mineflow\variable\object\ScoreboardVariable;
use aieuo\mineflow\variable\object\Vector3Variable;

class DummyVariable extends Variable {

    public int $type = Variable::DUMMY;

    private string $description;
    private string $valueType;

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
    public const LIVING = "living";
    public const ITEM = "item";
    public const WORLD = "world";
    public const LOCATION = "location";
    public const PLAYER = "player";
    public const POSITION = "position";
    public const VECTOR3 = "vector3";
    public const SCOREBOARD = "scoreboard";
    public const INVENTORY = "inventory";
    public const AXIS_ALIGNED_BB = "axisAlignedBB";
    public const RECIPE = "recipe";

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
        return match ($this->getValueType()) {
            self::BLOCK => BlockVariable::getValuesDummy(),
            self::CONFIG => ConfigVariable::getValuesDummy(),
            self::ENTITY => EntityVariable::getValuesDummy(),
            self::EVENT => EventVariable::getValuesDummy(),
            self::HUMAN => HumanVariable::getValuesDummy(),
            self::LIVING => LivingVariable::getValuesDummy(),
            self::ITEM => ItemVariable::getValuesDummy(),
            self::WORLD => WorldVariable::getValuesDummy(),
            self::LOCATION => LocationVariable::getValuesDummy(),
            self::PLAYER => PlayerVariable::getValuesDummy(),
            self::POSITION => PositionVariable::getValuesDummy(),
            self::VECTOR3 => Vector3Variable::getValuesDummy(),
            self::SCOREBOARD => ScoreboardVariable::getValuesDummy(),
            self::INVENTORY => InventoryVariable::getValuesDummy(),
            self::AXIS_ALIGNED_BB => AxisAlignedBBVariable::getValuesDummy(),
            self::RECIPE => RecipeVariable::getValuesDummy(),
            default => [],
        };
    }

    public function isObjectVariableType(): bool {
        return in_array($this->getValueType(), [
            self::BLOCK,
            self::CONFIG,
            self::ENTITY,
            self::EVENT,
            self::HUMAN,
            self::LIVING,
            self::ITEM,
            self::WORLD,
            self::LOCATION,
            self::PLAYER,
            self::POSITION,
            self::VECTOR3,
            self::SCOREBOARD,
            self::INVENTORY,
            self::AXIS_ALIGNED_BB,
            self::RECIPE,
        ], true);
    }
}