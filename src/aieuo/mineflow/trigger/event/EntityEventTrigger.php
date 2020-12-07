<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\event\entity\EntityEvent;
use pocketmine\event\player\PlayerEvent;

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