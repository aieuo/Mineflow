<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\WorldObjectVariable;
use pocketmine\event\entity\EntityLevelChangeEvent;

class EntityLevelChangeEventTrigger extends PlayerEventTrigger {
    public function __construct(string $subKey = "") {
        parent::__construct(EntityLevelChangeEvent::class, $subKey);
    }

    public function getVariables(mixed $event): array {
        /** @var EntityLevelChangeEvent $event */
        $target = $event->getEntity();
        $variables = DefaultVariables::getEntityVariables($target);
        $variables["origin_world"] = new WorldObjectVariable($event->getOrigin());
        $variables["target_world"] = new WorldObjectVariable($event->getTarget());
        return $variables;
    }

    public function getVariablesDummy(): array {
        return [
            "target" => new DummyVariable(DummyVariable::PLAYER),
            "origin_world" => new DummyVariable(DummyVariable::WORLD),
            "target_world" => new DummyVariable(DummyVariable::WORLD),
        ];
    }
}