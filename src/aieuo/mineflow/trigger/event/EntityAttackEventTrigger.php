<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\object\PlayerVariable;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;

class EntityAttackEventTrigger extends PlayerEventTrigger {
    public function __construct() {
        parent::__construct("EntityAttackEvent", EntityDamageByEntityEvent::class);
    }

    public function getTargetEntity(Event $event): ?Entity {
        /** @var $event EntityDamageByEntityEvent */
        return $event->getDamager();
    }

    public function getVariables($event): array {
        /** @var EntityDamageByEntityEvent $event */
        $target = $event->getEntity();
        $variables = DefaultVariables::getEntityVariables($target, "damaged");
        $variables["damage"] = new NumberVariable($event->getBaseDamage());
        $variables["cause"] = new NumberVariable($event->getCause());
        return array_merge($variables, DefaultVariables::getEntityVariables($event->getDamager(), "target"));
    }

    public function getVariablesDummy(): array {
        return [
            "target" => new DummyVariable(PlayerVariable::class),
            "damage" => new DummyVariable(NumberVariable::class),
            "cause" => new DummyVariable(NumberVariable::class),
            "damaged" => new DummyVariable(PlayerVariable::class),
        ];
    }
}