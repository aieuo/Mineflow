<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\entity\Entity;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Event;

class BlockBreakEventTrigger extends EventTrigger {
    public function __construct(string $subKey = "") {
        parent::__construct(BlockBreakEvent::class, $subKey);
    }

    public function getTargetEntity(Event $event): ?Entity {
        /** @var $event BlockBreakEvent */
        return $event->getPlayer();
    }

    public function getVariables($event): array {
        /** @var BlockBreakEvent $event */
        $target = $event->getPlayer();
        $block = $event->getBlock();
        return array_merge(DefaultVariables::getPlayerVariables($target), DefaultVariables::getBlockVariables($block));
    }

    public function getVariablesDummy(): array {
        return [
            "target" => new DummyVariable("target", DummyVariable::PLAYER),
            "block" => new DummyVariable("block", DummyVariable::BLOCK),
        ];
    }
}