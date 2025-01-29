<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\object\ItemVariable;
use aieuo\mineflow\variable\object\PlayerVariable;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\item\Item;

class CraftItemEventTrigger extends EventTrigger {
    public function __construct() {
        parent::__construct("CraftItemEvent", CraftItemEvent::class);
    }

    public function getVariables(mixed $event): array {
        /** @var CraftItemEvent $event */
        $target = $event->getPlayer();
        $inputs = array_map(fn(Item $input) => new ItemVariable($input), array_values($event->getInputs()));
        $outputs = array_map(fn(Item $output) => new ItemVariable($output), array_values($event->getOutputs()));
        $variables = DefaultVariables::getPlayerVariables($target);
        $variables["inputs"] = new ListVariable($inputs);
        $variables["outputs"] = new ListVariable($outputs);
        return $variables;
    }

    public function getVariablesDummy(): array {
        return [
            "target" => new DummyVariable(PlayerVariable::class),
            "inputs" => new DummyVariable(ListVariable::class, ItemVariable::getTypeName()),
            "outputs" => new DummyVariable(ListVariable::class, ItemVariable::getTypeName()),
        ];
    }
}