<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\LevelObjectVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\event\level\LevelLoadEvent;

class LevelLoadEventTrigger extends EventTrigger {
    public function __construct(string $subKey = "") {
        parent::__construct(LevelLoadEvent::class, $subKey);
    }

    /**
     * @param LevelLoadEvent $event
     * @return array<string, Variable>
     * @noinspection PhpMissingParamTypeInspection
     */
    public function getVariables($event): array {
        return ["level" => new LevelObjectVariable($event->getLevel(), "level")];
    }

    public function getVariablesDummy(): array {
        return [
            "level" => new DummyVariable("level", DummyVariable::LEVEL),
        ];
    }
}