<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\BooleanVariable;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\VariableProperty;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\entity\Location;
use pocketmine\math\Facing;
use pocketmine\player\Player;

class EntityVariable extends ObjectVariable {

    public static function fromObject(Entity $entity): EntityVariable|LivingVariable|HumanVariable|PlayerVariable {
        return match (true) {
            $entity instanceof Player => new PlayerVariable($entity),
            $entity instanceof Human => new HumanVariable($entity),
            $entity instanceof Living => new LivingVariable($entity),
            default => new EntityVariable($entity),
        };
    }

    public static function getTypeName(): string {
        return "entity";
    }

    public function __construct(private Entity $entity) {
    }

    public function getValue(): Entity {
        return $this->entity;
    }

    public function __toString(): string {
        $name = $this->getValue()->getNameTag();
        return empty($name) ? (string)$this->getValue() : $name;
    }

    public static function registerProperties(string $class = self::class): void {
        self::registerProperty($class, "id", new VariableProperty(
            new DummyVariable(NumberVariable::class),
            fn(Entity $entity) => new NumberVariable($entity->getId()),
        ));
        self::registerProperty($class, "saveId", new VariableProperty(
            new DummyVariable(StringVariable::class),
            function (Entity $entity) {
                try {
                    return new StringVariable(EntityFactory::getInstance()->getSaveId($entity::class));
                } catch (\InvalidArgumentException) {
                    return new StringVariable("");
                }
            }
        ));
        self::registerProperty($class, "nameTag", new VariableProperty(
            new DummyVariable(StringVariable::class),
            fn(Entity $entity) => new StringVariable($entity->getNameTag()),
        ));
        self::registerProperty($class, "health", new VariableProperty(
            new DummyVariable(NumberVariable::class),
            fn(Entity $entity) => new NumberVariable($entity->getHealth()),
        ));
        self::registerProperty($class, "maxHealth", new VariableProperty(
            new DummyVariable(NumberVariable::class),
            fn(Entity $entity) => new NumberVariable($entity->getMaxHealth()),
        ));
        self::registerProperty($class, "direction", new VariableProperty(
            new DummyVariable(NumberVariable::class),
            fn(Entity $entity) => new NumberVariable($entity->getHorizontalFacing()),
        ));
        self::registerProperty($class, "directionVector", new VariableProperty(
            new DummyVariable(Vector3Variable::class),
            fn(Entity $entity) => new Vector3Variable($entity->getDirectionVector()),
        ), aliases: ["direction_vector"]);
        self::registerProperty($class, "onGround", new VariableProperty(
            new DummyVariable(BooleanVariable::class),
            fn(Entity $entity) => new BooleanVariable($entity->isOnGround()),
        ));
        self::registerProperty($class, "bounding_box", new VariableProperty(
            new DummyVariable(AxisAlignedBBVariable::class),
            fn(Entity $entity) => new AxisAlignedBBVariable($entity->getBoundingBox()),
        ));
        self::registerProperty($class, "isVisible", new VariableProperty(
            new DummyVariable(BooleanVariable::class),
            fn(Entity $entity) => new BooleanVariable(!$entity->isInvisible()),
        ));
        self::registerProperty($class, "location", new VariableProperty(
            new DummyVariable(LocationVariable::class),
            fn(Entity $entity) => new LocationVariable($entity->getLocation()),
        ));
        self::registerProperty($class, "yaw", new VariableProperty(
            new DummyVariable(NumberVariable::class),
            fn(Entity $entity) => new NumberVariable($entity->getLocation()->getYaw()),
        ));
        self::registerProperty($class, "pitch", new VariableProperty(
            new DummyVariable(NumberVariable::class),
            fn(Entity $entity) => new NumberVariable($entity->getLocation()->getPitch()),
        ));
        self::registerProperty($class, "position", new VariableProperty(
            new DummyVariable(LocationVariable::class),
            fn(Entity $entity) => new PositionVariable($entity->getLocation()->asPosition()),
        ));
        self::registerProperty($class, "world", new VariableProperty(
            new DummyVariable(WorldVariable::class),
            fn(Entity $entity) => new WorldVariable($entity->getWorld()),
        ));
        self::registerProperty($class, "x", new VariableProperty(
            new DummyVariable(NumberVariable::class),
            fn(Entity $entity) => new NumberVariable($entity->getLocation()->getX()),
        ));
        self::registerProperty($class, "y", new VariableProperty(
            new DummyVariable(NumberVariable::class),
            fn(Entity $entity) => new NumberVariable($entity->getLocation()->getY()),
        ));
        self::registerProperty($class, "z", new VariableProperty(
            new DummyVariable(NumberVariable::class),
            fn(Entity $entity) => new NumberVariable($entity->getLocation()->getZ()),
        ));
        self::registerProperty($class, "xyz", new VariableProperty(
            new DummyVariable(StringVariable::class),
            fn(Entity $entity) => new StringVariable($entity->getLocation()->getX().",".$entity->getLocation()->getY().",".$entity->getLocation()->getZ()),
        ));
        foreach (["down" => Facing::DOWN, "up" => Facing::UP, "north" => Facing::NORTH, "south" => Facing::SOUTH, "west" => Facing::WEST, "east" => Facing::EAST] as $name => $facing) {
            self::registerProperty($class, $name, new VariableProperty(
                new DummyVariable(LocationVariable::class),
                fn(Entity $entity) => new LocationVariable(self::getSideLocation($entity, $facing)),
            ));
        }
    }

    private static function getSideLocation(Entity $entity, int $facing): Location {
        $location = $entity->getLocation();
        return Location::fromObject($location->getSide($facing), $location->getWorld(), $location->getYaw(), $location->getPitch());
    }
}