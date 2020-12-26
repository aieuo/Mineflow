<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use pocketmine\event\entity\EntityEvent;

class EntityEventTrigger extends EventTrigger {

    public function getVariables($event): array {
        /** @var EntityEvent $event */
        $target = $event->getEntity();
        return array_merge(DefaultVariables::getEntityVariables($target));
    }

    public function getVariablesDummy(): array {
        return [
            "target" => new DummyVariable("target", DummyVariable::ENTITY),
        ];
    }
}