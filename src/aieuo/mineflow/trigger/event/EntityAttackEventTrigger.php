<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\event\EntityAttackEvent;
use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\object\PlayerObjectVariable;
use pocketmine\entity\Entity;
use pocketmine\event\Event;

class EntityAttackEventTrigger extends PlayerEventTrigger {
    public function __construct(string $subKey = "") {
        parent::__construct(EntityAttackEvent::class, $subKey);
    }

    public function getTargetEntity(Event $event): ?Entity {
        /** @var $event EntityAttackEvent */
        return $event->getDamageEvent()->getDamager();
    }

    public function getVariables($event): array {
        /** @var EntityAttackEvent $event */
        $entityDamageEvent = $event->getDamageEvent();
        $target = $entityDamageEvent->getEntity();
        $variables = DefaultVariables::getEntityVariables($target, "damaged");
        $variables["damage"] = new NumberVariable($entityDamageEvent->getBaseDamage());
        $variables["cause"] = new NumberVariable($entityDamageEvent->getCause());
        return array_merge($variables, DefaultVariables::getEntityVariables($entityDamageEvent->getDamager(), "target"));
    }

    public function getVariablesDummy(): array {
        return [
            "target" => new DummyVariable(PlayerObjectVariable::class),
            "damage" => new DummyVariable(NumberVariable::class),
            "cause" => new DummyVariable(NumberVariable::class),
            "damaged" => new DummyVariable(PlayerObjectVariable::class),
        ];
    }
}