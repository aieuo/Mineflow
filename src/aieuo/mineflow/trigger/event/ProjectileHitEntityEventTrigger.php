<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\event\entity\ProjectileHitEntityEvent;

class ProjectileHitEntityEventTrigger extends EventTrigger {
    public function __construct(string $subKey = "") {
        parent::__construct(ProjectileHitEntityEvent::class, $subKey);
    }

    /**
     * @param ProjectileHitEntityEvent $event
     * @return array<string, Variable>
     * @noinspection PhpMissingParamTypeInspection
     */
    public function getVariables($event): array {
        $variables = DefaultVariables::getEntityVariables($event->getEntityHit());
        return array_merge($variables, DefaultVariables::getEntityVariables($event->getEntity(), "projective"));
    }

    public function getVariablesDummy(): array {
        return [
            "target" => new DummyVariable("target", DummyVariable::ENTITY),
            "projective" => new DummyVariable("projective", DummyVariable::ENTITY),
        ];
    }
}