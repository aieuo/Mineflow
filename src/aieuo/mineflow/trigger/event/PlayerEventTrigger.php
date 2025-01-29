<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\PlayerVariable;
use pocketmine\event\player\PlayerEvent;

class PlayerEventTrigger extends EventTrigger {

    public function getVariables(mixed $event): array {
        /** @var PlayerEvent $event */
        $target = $event->getPlayer();
        return array_merge(DefaultVariables::getPlayerVariables($target));
    }

    public function getVariablesDummy(): array {
        return [
            "target" => new DummyVariable(PlayerVariable::class),
        ];
    }
}