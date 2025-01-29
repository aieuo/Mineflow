<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\ItemVariable;
use pocketmine\event\inventory\FurnaceSmeltEvent;

class FurnaceSmeltEventTrigger extends EventTrigger {
    public function __construct() {
        parent::__construct("FurnaceSmeltEvent", FurnaceSmeltEvent::class);
    }

    public function getVariables(mixed $event): array {
        /** @var FurnaceSmeltEvent $event */
        $result = $event->getResult();
        $source = $event->getSource();
        return [
            "result" => new ItemVariable($result),
            "source" => new ItemVariable($source),
        ];
    }

    public function getVariablesDummy(): array {
        return [
            "result" => new DummyVariable(ItemVariable::class),
            "source" => new DummyVariable(ItemVariable::class),
        ];
    }
}