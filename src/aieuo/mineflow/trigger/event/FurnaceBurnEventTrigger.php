<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\ItemObjectVariable;
use pocketmine\event\inventory\FurnaceBurnEvent;

class FurnaceBurnEventTrigger extends EventTrigger {
    public function __construct(string $subKey = "") {
        parent::__construct(FurnaceBurnEvent::class, $subKey);
    }

    public function getVariables(mixed $event): array {
        /** @var FurnaceBurnEvent $event */
        $fuel = $event->getFuel();
        return ["fuel" => new ItemObjectVariable($fuel),];
    }

    public function getVariablesDummy(): array {
        return [
            "fuel" => new DummyVariable(ItemObjectVariable::class)
        ];
    }
}