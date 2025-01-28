<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\LocationVariable;
use aieuo\mineflow\variable\object\PlayerVariable;
use pocketmine\event\player\PlayerMoveEvent;

class PlayerMoveEventTrigger extends PlayerEventTrigger {
    public function __construct() {
        parent::__construct("PlayerMoveEvent", PlayerMoveEvent::class);
    }

    public function getVariables(mixed $event): array {
        /** @var PlayerMoveEvent $event */
        $variables = [
            "move_from" => new LocationVariable($event->getFrom()),
            "move_to" => new LocationVariable($event->getTo())
        ];
        $target = $event->getPlayer();
        return array_merge($variables, DefaultVariables::getPlayerVariables($target));
    }

    public function getVariablesDummy(): array {
        return [
            "move_from" => new DummyVariable(LocationVariable::class),
            "move_to" => new DummyVariable(LocationVariable::class),
            "target" => new DummyVariable(PlayerVariable::class),
        ];
    }
}