<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\ItemVariable;
use pocketmine\event\inventory\FurnaceBurnEvent;

class FurnaceBurnEventTrigger extends EventTrigger {
    public function __construct() {
        parent::__construct("FurnaceBurnEvent", FurnaceBurnEvent::class);
    }

    public function getVariables(mixed $event): array {
        /** @var FurnaceBurnEvent $event */
        $fuel = $event->getFuel();
        return ["fuel" => new ItemVariable($fuel),];
    }

    public function getVariablesDummy(): array {
        return [
            "fuel" => new DummyVariable(ItemVariable::class)
        ];
    }
}