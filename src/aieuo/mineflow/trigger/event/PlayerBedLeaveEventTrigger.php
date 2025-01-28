<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\BlockVariable;
use aieuo\mineflow\variable\object\PlayerVariable;
use pocketmine\event\player\PlayerBedLeaveEvent;

class PlayerBedLeaveEventTrigger extends EventTrigger {
    public function __construct() {
        parent::__construct("PlayerBedLeaveEvent", PlayerBedLeaveEvent::class);
    }

    public function getVariables(mixed $event): array {
        /** @var PlayerBedLeaveEvent $event */
        $target = $event->getPlayer();
        $block = $event->getBed();
        return array_merge(DefaultVariables::getPlayerVariables($target), DefaultVariables::getBlockVariables($block));
    }

    public function getVariablesDummy(): array {
        return [
            "target" => new DummyVariable(PlayerVariable::class),
            "block" => new DummyVariable(BlockVariable::class),
        ];
    }
}
