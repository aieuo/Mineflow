<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\BlockVariable;
use aieuo\mineflow\variable\object\PlayerVariable;
use pocketmine\event\player\PlayerInteractEvent;

class PlayerInteractEventTrigger extends PlayerEventTrigger {
    public function __construct() {
        parent::__construct("PlayerInteractEvent", PlayerInteractEvent::class);
    }

    public function getVariables(mixed $event): array {
        /** @var PlayerInteractEvent $event */
        $target = $event->getPlayer();
        $block = $event->getBlock();
        return array_merge(DefaultVariables::getPlayerVariables($target), DefaultVariables::getBlockVariables($block));
    }

    public function getVariablesDummy(): array {
        return [
            "target" => new DummyVariable(PlayerVariable::class),
            "block" => new DummyVariable(BlockVariable::class),
        ];
    }
}