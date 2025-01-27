<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\ItemVariable;
use aieuo\mineflow\variable\object\PlayerVariable;
use pocketmine\event\player\PlayerDropItemEvent;

class PlayerDropItemEventTrigger extends EventTrigger {
    public function __construct() {
        parent::__construct("PlayerDropItemEvent", PlayerDropItemEvent::class);
    }

    public function getVariables(mixed $event): array {
        /** @var PlayerDropItemEvent $event */
        $target = $event->getPlayer();
        $item = $event->getItem();
        return array_merge(DefaultVariables::getPlayerVariables($target), [
            "item" => new ItemVariable($item),
        ]);
    }

    public function getVariablesDummy(): array {
        return [
            "target" => new DummyVariable(PlayerVariable::class),
            "item" => new DummyVariable(ItemVariable::class),
        ];
    }
}