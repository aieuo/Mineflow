<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\ItemObjectVariable;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\inventory\PlayerInventory;

class InventoryPickupItemEventTrigger extends EventTrigger {
    public function __construct(string $subKey = "") {
        parent::__construct(InventoryPickupItemEvent::class, $subKey);
    }

    public function getVariables($event): array {
        $variables = [];
        /** @var InventoryPickupItemEvent $event */
        $inventory = $event->getInventory();
        if ($inventory instanceof PlayerInventory) {
            $variables = array_merge($variables, DefaultVariables::getEntityVariables($inventory->getHolder()));
        }
        $item = $event->getItem()->getItem();
        return array_merge($variables, [
            "item" => new ItemObjectVariable($item),
        ]);
    }

    public function getVariablesDummy(): array {
        return [
            "target" => new DummyVariable("target", DummyVariable::PLAYER),
            "item" => new DummyVariable("item", DummyVariable::ITEM),
        ];
    }
}