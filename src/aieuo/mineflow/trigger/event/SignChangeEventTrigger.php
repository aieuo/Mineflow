<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\event\block\SignChangeEvent;

class SignChangeEventTrigger extends PlayerEventTrigger {
    public function __construct(string $subKey = "") {
        parent::__construct(SignChangeEvent::class, $subKey);
    }

    /**
     * @param SignChangeEvent $event
     * @return array<string, Variable>
     * @noinspection PhpMissingParamTypeInspection
     */
    public function getVariables($event): array {
        $lines = $event->getLines();
        $target = $event->getPlayer();
        $block = $event->getBlock();
        $variables = ["sign_lines" => new ListVariable(array_map(function (string $line) { return new StringVariable($line); }, $lines), "sign_lines")];
        return array_merge($variables, DefaultVariables::getPlayerVariables($target), DefaultVariables::getBlockVariables($block));
    }

    public function getVariablesDummy(): array {
        return [
            "sign_lines" => new DummyVariable("sign_lines", DummyVariable::LIST),
            "target" => new DummyVariable("target", DummyVariable::PLAYER),
            "block" => new DummyVariable("block", DummyVariable::BLOCK),
        ];
    }
}