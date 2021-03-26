<?php

declare(strict_types=1);

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\WorldObjectVariable;
use pocketmine\event\level\LevelLoadEvent;

class LevelLoadEventTrigger extends EventTrigger {
    public function __construct(string $subKey = "") {
        parent::__construct(LevelLoadEvent::class, $subKey);
    }

    public function getVariables($event): array {
        /** @var LevelLoadEvent $event */
        return ["world" => new WorldObjectVariable($event->getLevel())];
    }

    public function getVariablesDummy(): array {
        return [
            "world" => new DummyVariable(DummyVariable::WORLD),
        ];
    }
}