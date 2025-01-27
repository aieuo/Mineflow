<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\BooleanVariable;
use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\PlayerVariable;
use pocketmine\event\player\PlayerToggleSprintEvent;

class PlayerToggleSprintEventTrigger extends EventTrigger {
    public function __construct() {
        parent::__construct("PlayerToggleSprintEvent", PlayerToggleSprintEvent::class);
    }

    public function getVariables(mixed $event): array {
        /** @var PlayerToggleSprintEvent $event */
        $target = $event->getPlayer();
        $variables = DefaultVariables::getPlayerVariables($target);
        $variables["state"] = new BooleanVariable($event->isSprinting());
        return $variables;
    }

    public function getVariablesDummy(): array {
        return [
            "target" => new DummyVariable(PlayerVariable::class),
            "state" => new DummyVariable(BooleanVariable::class),
        ];
    }
}