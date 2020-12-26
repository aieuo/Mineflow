<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\StringVariable;
use pocketmine\event\player\PlayerToggleFlightEvent;

class PlayerToggleFlightEventTrigger extends EventTrigger {
    public function __construct(string $subKey = "") {
        parent::__construct(PlayerToggleFlightEvent::class, $subKey);
    }

    public function getVariables($event): array {
        /** @var PlayerToggleFlightEvent $event */
        $target = $event->getPlayer();
        $variables = DefaultVariables::getPlayerVariables($target);
        $variables["state"] = new StringVariable($event->isFlying() ? "true" : "false", "state");
        return $variables;
    }

    public function getVariablesDummy(): array {
        return [
            "target" => new DummyVariable("target", DummyVariable::PLAYER),
            "state" => new DummyVariable("state", DummyVariable::STRING),
        ];
    }
}