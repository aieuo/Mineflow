<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\LevelObjectVariable;
use pocketmine\event\level\LevelLoadEvent;

class LevelLoadEventTrigger extends EventTrigger {
    public function __construct(string $subKey = "") {
        parent::__construct(LevelLoadEvent::class, $subKey);
    }

    public function getVariables($event): array {
        /** @var LevelLoadEvent $event */
        return ["level" => new LevelObjectVariable($event->getLevel())];
    }

    public function getVariablesDummy(): array {
        return [
            "level" => new DummyVariable(DummyVariable::LEVEL),
        ];
    }
}