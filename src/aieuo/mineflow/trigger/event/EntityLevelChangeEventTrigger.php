<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\PlayerVariable;
use aieuo\mineflow\variable\object\WorldVariable;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Event;

class EntityLevelChangeEventTrigger extends PlayerEventTrigger {
    public function __construct() {
        parent::__construct("EntityLevelChangeEvent", EntityTeleportEvent::class);
    }

    public function filter(Event $event): bool {
        /** @var EntityTeleportEvent $event */
        return $event->getFrom()->getWorld() !== $event->getTo()->getWorld();
    }

    public function getVariables(mixed $event): array {
        /** @var EntityTeleportEvent $event */
        $target = $event->getEntity();
        $variables = DefaultVariables::getEntityVariables($target);
        $variables["origin_world"] = new WorldVariable($event->getFrom()->getWorld());
        $variables["target_world"] = new WorldVariable($event->getTo()->getWorld());
        return $variables;
    }

    public function getVariablesDummy(): array {
        return [
            "target" => new DummyVariable(PlayerVariable::class),
            "origin_world" => new DummyVariable(WorldVariable::class),
            "target_world" => new DummyVariable(WorldVariable::class),
        ];
    }
}