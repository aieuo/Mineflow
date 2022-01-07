<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\StringVariable;
use pocketmine\event\block\SignChangeEvent;

class SignChangeEventTrigger extends PlayerEventTrigger {
    public function __construct(string $subKey = "") {
        parent::__construct("SignChangeEvent", $subKey, SignChangeEvent::class);
    }

    public function getVariables(mixed $event): array {
        /** @var SignChangeEvent $event */
        $lines = $event->getNewText()->getLines();
        $target = $event->getPlayer();
        $block = $event->getBlock();
        $variables = ["sign_lines" => new ListVariable(array_map(fn(string $line) => new StringVariable($line), $lines), "sign_lines")];
        return array_merge($variables, DefaultVariables::getPlayerVariables($target), DefaultVariables::getBlockVariables($block));
    }

    public function getVariablesDummy(): array {
        return [
            "sign_lines" => new DummyVariable(DummyVariable::LIST, DummyVariable::STRING),
            "target" => new DummyVariable(DummyVariable::PLAYER),
            "block" => new DummyVariable(DummyVariable::BLOCK),
        ];
    }
}