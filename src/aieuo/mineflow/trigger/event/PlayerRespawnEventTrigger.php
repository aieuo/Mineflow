<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\PlayerVariable;
use aieuo\mineflow\variable\object\PositionVariable;
use pocketmine\event\player\PlayerRespawnEvent;

class PlayerRespawnEventTrigger extends EventTrigger {
    public function __construct() {
        parent::__construct("PlayerRespawnEvent", PlayerRespawnEvent::class);
    }

    public function getVariables(mixed $event): array {
        /** @var PlayerRespawnEvent $event */
        $target = $event->getPlayer();
        $position = $event->getRespawnPosition();
        return array_merge(DefaultVariables::getPlayerVariables($target), [
            "position" => new PositionVariable($position),
        ]);
    }

    public function getVariablesDummy(): array {
        return [
            "target" => new DummyVariable(PlayerVariable::class),
            "position" => new DummyVariable(PositionVariable::class),
        ];
    }
}