<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use pocketmine\event\player\PlayerInteractEvent;

class PlayerInteractEventTrigger extends PlayerEventTrigger {
    public function __construct(string $subKey = "") {
        parent::__construct(PlayerInteractEvent::class, $subKey);
    }

    public function getVariables($event): array {
        /** @var PlayerInteractEvent $event */
        $target = $event->getPlayer();
        $block = $event->getBlock();
        return array_merge(DefaultVariables::getPlayerVariables($target), DefaultVariables::getBlockVariables($block));
    }

    public function getVariablesDummy(): array {
        return [
            "target" => new DummyVariable(DummyVariable::PLAYER),
            "block" => new DummyVariable(DummyVariable::BLOCK),
        ];
    }
}