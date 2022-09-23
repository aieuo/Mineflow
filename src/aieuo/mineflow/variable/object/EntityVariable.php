<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\BooleanVariable;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\player\Player;

class EntityVariable extends PositionVariable {

    public static function fromObject(Entity $entity, ?string $str = null): EntityVariable|LivingVariable|HumanVariable|PlayerVariable {
        return match (true) {
            $entity instanceof Player => new PlayerVariable($entity, $str ?? $entity->getName()),
            $entity instanceof Human => new HumanVariable($entity, $str ?? $entity->getNameTag()),
            $entity instanceof Living => new LivingVariable($entity),
            default => new EntityVariable($entity),
        };
    }

    public function __construct(private Entity $entity, ?string $str = null) {
        parent::__construct($this->entity->getPosition(), $str);
    }

    public function getValueFromIndex(string $index): ?Variable {
        $entity = $this->getEntity();
        switch ($index) {
            case "id":
                return new NumberVariable($entity->getId());
            case "saveId":
                try {
                    return new StringVariable(EntityFactory::getInstance()->getSaveId($entity::class));
                } catch (\InvalidArgumentException) {
                    return new StringVariable("");
                }
            case "nameTag":
                return new StringVariable($entity->getNameTag());
            case "health":
                return new NumberVariable($entity->getHealth());
            case "maxHealth":
                return new NumberVariable($entity->getMaxHealth());
            case "yaw":
                return new NumberVariable($entity->getLocation()->getYaw());
            case "pitch":
                return new NumberVariable($entity->getLocation()->getPitch());
            case "direction":
                return new NumberVariable($entity->getHorizontalFacing());
            case "onGround":
                return new BooleanVariable($entity->isOnGround());
            case "bounding_box":
                return new AxisAlignedBBVariable($entity->getBoundingBox());
            default:
                return parent::getValueFromIndex($index);
        }
    }

    public function getEntity(): Entity {
        return $this->entity;
    }

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "id" => new DummyVariable(DummyVariable::NUMBER),
            "saveId" => new DummyVariable(DummyVariable::STRING),
            "nameTag" => new DummyVariable(DummyVariable::STRING),
            "health" => new DummyVariable(DummyVariable::NUMBER),
            "maxHealth" => new DummyVariable(DummyVariable::NUMBER),
            "yaw" => new DummyVariable(DummyVariable::NUMBER),
            "pitch" => new DummyVariable(DummyVariable::NUMBER),
            "direction" => new DummyVariable(DummyVariable::NUMBER),
            "onGround" => new DummyVariable(DummyVariable::BOOLEAN),
            "bounding_box" => new DummyVariable(DummyVariable::AXIS_ALIGNED_BB),
        ]);
    }

    public function __toString(): string {
        $name = $this->getEntity()->getNameTag();
        return empty($name) ? (string)$this->getEntity() : $name;
    }
}