<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\object\PlayerObjectVariable;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;

class EntityDamageEventTrigger extends PlayerEventTrigger {
    public function __construct(string $subKey = "") {
        parent::__construct(EntityDamageEvent::class, $subKey);
    }

    public function getVariables(mixed $event): array {
        /** @var EntityDamageEvent $event */
        $target = $event->getEntity();
        $variables = DefaultVariables::getEntityVariables($target, "target");
        $variables["damage"] = new NumberVariable($event->getBaseDamage());
        $variables["cause"] = new NumberVariable($event->getCause());
        if ($event instanceof EntityDamageByEntityEvent) {
            $damager = $event->getDamager();
            $variables = array_merge($variables, DefaultVariables::getEntityVariables($damager, "damager"));
        }
        return $variables;
    }

    public function getVariablesDummy(): array {
        return [
            "target" => new DummyVariable(PlayerObjectVariable::class),
            "damage" => new DummyVariable(NumberVariable::class),
            "cause" => new DummyVariable(NumberVariable::class),
            "damager" => new DummyVariable(PlayerObjectVariable::class),
        ];
    }
}