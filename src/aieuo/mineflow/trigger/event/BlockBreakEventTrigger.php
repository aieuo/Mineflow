<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\BlockVariable;
use aieuo\mineflow\variable\object\PlayerVariable;
use pocketmine\entity\Entity;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Event;

class BlockBreakEventTrigger extends EventTrigger {
    public function __construct() {
        parent::__construct("BlockBreakEvent", BlockBreakEvent::class);
    }

    public function getTargetEntity(Event $event): ?Entity {
        /** @var $event BlockBreakEvent */
        return $event->getPlayer();
    }

    public function getVariables(mixed $event): array {
        /** @var BlockBreakEvent $event */
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