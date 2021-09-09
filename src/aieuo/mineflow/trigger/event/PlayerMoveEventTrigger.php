<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\LocationObjectVariable;
use aieuo\mineflow\variable\object\PlayerObjectVariable;
use pocketmine\event\player\PlayerMoveEvent;

class PlayerMoveEventTrigger extends PlayerEventTrigger {
    public function __construct(string $subKey = "") {
        parent::__construct(PlayerMoveEvent::class, $subKey);
    }

    public function getVariables(mixed $event): array {
        /** @var PlayerMoveEvent $event */
        $variables = [
            "move_from" => new LocationObjectVariable($event->getFrom()),
            "move_to" => new LocationObjectVariable($event->getTo())
        ];
        $target = $event->getPlayer();
        return array_merge($variables, DefaultVariables::getPlayerVariables($target));
    }

    public function getVariablesDummy(): array {
        return [
            "move_from" => new DummyVariable(LocationObjectVariable::class),
            "move_to" => new DummyVariable(LocationObjectVariable::class),
            "target" => new DummyVariable(PlayerObjectVariable::class),
        ];
    }
}