<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\BlockVariable;
use aieuo\mineflow\variable\object\PlayerVariable;
use pocketmine\entity\Entity;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Event;

class BlockPlaceEventTrigger extends EventTrigger {
    public function __construct() {
        parent::__construct("BlockPlaceEvent", BlockPlaceEvent::class);
    }

    public function getTargetEntity(Event $event): ?Entity {
        /** @var $event BlockPlaceEvent */
        return $event->getPlayer();
    }

    public function getVariables(mixed $event): array {
        /** @var BlockPlaceEvent $event */
        $target = $event->getPlayer();
        $block = $event->getBlockAgainst();
        return array_merge(DefaultVariables::getPlayerVariables($target), DefaultVariables::getBlockVariables($block));
    }

    public function getVariablesDummy(): array {
        return [
            "target" => new DummyVariable(PlayerVariable::class),
            "block" => new DummyVariable(BlockVariable::class),
        ];
    }
}