<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\event\EntityAttackEvent;
use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\Variable;
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

    /**
     * @param EntityAttackEvent $event
     * @return array<string, Variable>
     * @noinspection PhpMissingParamTypeInspection
     */
    public function getVariables($event): array {
        $entityDamageEvent = $event->getDamageEvent();
        $target = $entityDamageEvent->getEntity();
        $variables = DefaultVariables::getEntityVariables($target, "damaged");
        $variables["damage"] = new NumberVariable($entityDamageEvent->getBaseDamage(), "damage");
        $variables["cause"] = new NumberVariable($entityDamageEvent->getCause(), "cause");
        $variables = array_merge($variables, DefaultVariables::getEntityVariables($entityDamageEvent->getDamager(), "target"));
        return $variables;
    }

    public function getVariablesDummy(): array {
        return [
            "target" => new DummyVariable("target", DummyVariable::PLAYER),
            "damage" => new DummyVariable("damage", DummyVariable::NUMBER),
            "cause" => new DummyVariable("cause", DummyVariable::NUMBER),
            "damager" => new DummyVariable("damager", DummyVariable::PLAYER),
        ];
    }
}