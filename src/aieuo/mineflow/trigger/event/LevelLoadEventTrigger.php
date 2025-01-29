<?php

declare(strict_types=1);

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\WorldVariable;
use pocketmine\event\world\WorldLoadEvent;

class LevelLoadEventTrigger extends EventTrigger {
    public function __construct() {
        parent::__construct("LevelLoadEvent", WorldLoadEvent::class);
    }

    public function getVariables(mixed $event): array {
        /** @var WorldLoadEvent $event */
        return ["world" => new WorldVariable($event->getWorld())];
    }

    public function getVariablesDummy(): array {
        return [
            "world" => new DummyVariable(WorldVariable::class),
        ];
    }
}