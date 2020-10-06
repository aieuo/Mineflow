<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\ItemObjectVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\event\inventory\FurnaceBurnEvent;

class FurnaceBurnEventTrigger extends EventTrigger {
    public function __construct(string $subKey = "") {
        parent::__construct(FurnaceBurnEvent::class, $subKey);
    }

    /**
     * @param FurnaceBurnEvent $event
     * @return array<string, Variable>
     * @noinspection PhpMissingParamTypeInspection
     */
    public function getVariables($event): array {
        $fuel = $event->getFuel();
        return ["fuel" => new ItemObjectVariable($fuel, "fuel"),];
    }

    public function getVariablesDummy(): array {
        return ["fuel" => new DummyVariable("fuel". DummyVariable::ITEM)];
    }
}