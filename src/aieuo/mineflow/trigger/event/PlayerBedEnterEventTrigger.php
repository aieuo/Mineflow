<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\BlockVariable;
use aieuo\mineflow\variable\object\PlayerVariable;
use pocketmine\event\player\PlayerBedEnterEvent;

class PlayerBedEnterEventTrigger extends EventTrigger {
    public function __construct() {
        parent::__construct("PlayerBedEnterEvent", PlayerBedEnterEvent::class);
    }

    public function getVariables(mixed $event): array {
        /** @var PlayerBedEnterEvent $event */
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