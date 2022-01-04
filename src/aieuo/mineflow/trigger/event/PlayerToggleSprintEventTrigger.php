<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\BoolVariable;
use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use pocketmine\event\player\PlayerToggleSprintEvent;

class PlayerToggleSprintEventTrigger extends EventTrigger {
    public function __construct(string $subKey = "") {
        parent::__construct(PlayerToggleSprintEvent::class, $subKey);
    }

    public function getVariables(mixed $event): array {
        /** @var PlayerToggleSprintEvent $event */
        $target = $event->getPlayer();
        $variables = DefaultVariables::getPlayerVariables($target);
        $variables["state"] = new BoolVariable($event->isSprinting());
        return $variables;
    }

    public function getVariablesDummy(): array {
        return [
            "target" => new DummyVariable(DummyVariable::PLAYER),
            "state" => new DummyVariable(DummyVariable::BOOLEAN),
        ];
    }
}