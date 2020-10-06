<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\LocationObjectVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\event\player\PlayerMoveEvent;

class PlayerMoveEventTrigger extends PlayerEventTrigger {
    public function __construct(string $subKey = "") {
        parent::__construct(PlayerMoveEvent::class, $subKey);
    }

    /**
     * @param PlayerMoveEvent $event
     * @return array<string, Variable>
     * @noinspection PhpMissingParamTypeInspection
     */
    public function getVariables($event): array {
        $variables = [
            "move_from" => new LocationObjectVariable($event->getFrom(), "move_from"),
            "move_to" => new LocationObjectVariable($event->getTo(), "move_to")
        ];
        $target = $event->getPlayer();
        return array_merge($variables, DefaultVariables::getPlayerVariables($target));
    }

    public function getVariablesDummy(): array {
        return [
            "move_from" => new DummyVariable("move_from", DummyVariable::LOCATION),
            "move_to" => new DummyVariable("move_to", DummyVariable::LOCATION),
            "target" => new DummyVariable("target", DummyVariable::PLAYER),
        ];
    }
}