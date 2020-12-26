<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;

class EntityDamageEventTrigger extends PlayerEventTrigger {
    public function __construct(string $subKey = "") {
        parent::__construct(EntityDamageEvent::class, $subKey);
    }

    public function getVariables($event): array {
        /** @var EntityDamageEvent $event */
        $target = $event->getEntity();
        $variables = DefaultVariables::getEntityVariables($target, "target");
        $variables["damage"] = new NumberVariable($event->getBaseDamage(), "damage");
        $variables["cause"] = new NumberVariable($event->getCause(), "cause");
        if ($event instanceof EntityDamageByEntityEvent) {
            $damager = $event->getDamager();
            $variables = array_merge($variables, DefaultVariables::getEntityVariables($damager, "damager"));
        }
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