<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\LevelObjectVariable;
use pocketmine\event\entity\EntityLevelChangeEvent;

class EntityLevelChangeEventTrigger extends PlayerEventTrigger {
    public function __construct(string $subKey = "") {
        parent::__construct(EntityLevelChangeEvent::class, $subKey);
    }

    public function getVariables($event): array {
        /** @var EntityLevelChangeEvent $event */
        $target = $event->getEntity();
        $variables = DefaultVariables::getEntityVariables($target);
        $variables["origin_level"] = new LevelObjectVariable($event->getOrigin());
        $variables["target_level"] = new LevelObjectVariable($event->getTarget());
        return $variables;
    }

    public function getVariablesDummy(): array {
        return [
            "target" => new DummyVariable("target", DummyVariable::PLAYER),
            "origin_level" => new DummyVariable("origin_level", DummyVariable::LEVEL),
            "target_level" => new DummyVariable("target_level", DummyVariable::LEVEL),
        ];
    }
}