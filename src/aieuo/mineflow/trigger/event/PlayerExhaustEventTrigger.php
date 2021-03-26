<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use pocketmine\event\player\PlayerExhaustEvent;

class PlayerExhaustEventTrigger extends EventTrigger {
    public function __construct(string $subKey = "") {
        parent::__construct(PlayerExhaustEvent::class, $subKey);
    }

    public function getVariables($event): array {
        /** @var PlayerExhaustEvent $event */
        $target = $event->getPlayer();
        $variables = DefaultVariables::getEntityVariables($target);
        $variables["amount"] = new NumberVariable($event->getAmount());
        $variables["cause"] = new NumberVariable($event->getCause());
        return $variables;
    }

    public function getVariablesDummy(): array {
        return [
            "target" => new DummyVariable(DummyVariable::PLAYER),
            "amount" => new DummyVariable(DummyVariable::NUMBER),
            "cause" => new DummyVariable(DummyVariable::NUMBER),
        ];
    }
}