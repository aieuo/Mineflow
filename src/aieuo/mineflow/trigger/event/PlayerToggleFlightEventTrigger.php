<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\BoolVariable;
use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use pocketmine\event\player\PlayerToggleFlightEvent;

class PlayerToggleFlightEventTrigger extends EventTrigger {
    public function __construct(string $subKey = "") {
        parent::__construct(PlayerToggleFlightEvent::class, $subKey);
    }

    public function getVariables($event): array {
        /** @var PlayerToggleFlightEvent $event */
        $target = $event->getPlayer();
        $variables = DefaultVariables::getPlayerVariables($target);
        $variables["state"] = new BoolVariable($event->isFlying());
        return $variables;
    }

    public function getVariablesDummy(): array {
        return [
            "target" => new DummyVariable(DummyVariable::PLAYER),
            "state" => new DummyVariable(DummyVariable::BOOLEAN),
        ];
    }
}