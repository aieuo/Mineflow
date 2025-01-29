<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\EntityVariable;
use aieuo\mineflow\variable\object\PlayerVariable;
use pocketmine\event\entity\ProjectileHitEntityEvent;

class ProjectileHitEntityEventTrigger extends EventTrigger {
    public function __construct() {
        parent::__construct("ProjectileHitEntityEvent", ProjectileHitEntityEvent::class);
    }

    public function getVariables(mixed $event): array {
        /** @var ProjectileHitEntityEvent $event */
        $variables = DefaultVariables::getEntityVariables($event->getEntityHit());
        return array_merge($variables, DefaultVariables::getEntityVariables($event->getEntity(), "projective"));
    }

    public function getVariablesDummy(): array {
        return [
            "target" => new DummyVariable(PlayerVariable::class),
            "projective" => new DummyVariable(EntityVariable::class),
        ];
    }
}