<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\object\ItemObjectVariable;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\item\Item;

class CraftItemEventTrigger extends EventTrigger {
    public function __construct(string $subKey = "") {
        parent::__construct(CraftItemEvent::class, $subKey);
    }

    public function getVariables($event): array {
        /** @var CraftItemEvent $event */
        $target = $event->getPlayer();
        $inputs = array_map(function (Item $input) {
            return new ItemObjectVariable($input, $input->__toString());
        }, array_values($event->getInputs()));
        $outputs = array_map(function (Item $output) {
            return new ItemObjectVariable($output, $output->__toString());
        }, array_values($event->getOutputs()));
        $variables = DefaultVariables::getPlayerVariables($target);
        $variables["inputs"] = new ListVariable($inputs);
        $variables["outputs"] = new ListVariable($outputs);
        return $variables;
    }

    public function getVariablesDummy(): array {
        return [
            "target" => new DummyVariable("target", DummyVariable::PLAYER),
            "inputs" => new DummyVariable("inputs", DummyVariable::LIST),
            "outputs" => new DummyVariable("outputs", DummyVariable::LIST),
        ];
    }
}