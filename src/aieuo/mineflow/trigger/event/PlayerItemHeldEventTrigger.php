<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\object\ItemVariable;
use aieuo\mineflow\variable\object\PlayerVariable;
use pocketmine\event\player\PlayerItemHeldEvent;

class PlayerItemHeldEventTrigger extends EventTrigger {
    public function __construct() {
        parent::__construct("PlayerItemHeldEvent", PlayerItemHeldEvent::class);
    }

    public function getVariables(mixed $event): array {
        /** @var PlayerItemHeldEvent $event */
        $target = $event->getPlayer();
        $item = $event->getItem();
        $slot = $event->getSlot();
        return array_merge(DefaultVariables::getPlayerVariables($target), [
            "item" => new ItemVariable($item),
            "slot" => new NumberVariable($slot),
        ]);
    }

    public function getVariablesDummy(): array {
        return [
            "target" => new DummyVariable(PlayerVariable::class),
            "item" => new DummyVariable(ItemVariable::class),
            "slot" => new DummyVariable(NumberVariable::class),
        ];
    }
}