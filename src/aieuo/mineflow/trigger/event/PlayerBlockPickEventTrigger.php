<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\BlockVariable;
use aieuo\mineflow\variable\object\ItemVariable;
use aieuo\mineflow\variable\object\PlayerVariable;
use pocketmine\event\player\PlayerBlockPickEvent;

class PlayerBlockPickEventTrigger extends EventTrigger {
    public function __construct() {
        parent::__construct("PlayerBlockPickEvent", PlayerBlockPickEvent::class);
    }

    public function getVariables(mixed $event): array {
        /** @var PlayerBlockPickEvent $event */
        $target = $event->getPlayer();
        $block = $event->getBlock();
        $item = $event->getResultItem();
        return array_merge(DefaultVariables::getPlayerVariables($target), DefaultVariables::getBlockVariables($block), [
            "item" => new ItemVariable($item),
        ]);
    }

    public function getVariablesDummy(): array {
        return [
            "target" => new DummyVariable(PlayerVariable::class),
            "block" => new DummyVariable(BlockVariable::class),
            "item" => new DummyVariable(ItemVariable::class),
        ];
    }
}
