<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\object\PlayerVariable;
use pocketmine\event\player\PlayerExhaustEvent;

class PlayerExhaustEventTrigger extends EventTrigger {
    public function __construct() {
        parent::__construct("PlayerExhaustEvent", PlayerExhaustEvent::class);
    }

    public function getVariables(mixed $event): array {
        /** @var PlayerExhaustEvent $event */
        $target = $event->getPlayer();
        $variables = DefaultVariables::getEntityVariables($target);
        $variables["amount"] = new NumberVariable($event->getAmount());
        $variables["cause"] = new NumberVariable($event->getCause());
        return $variables;
    }

    public function getVariablesDummy(): array {
        return [
            "target" => new DummyVariable(PlayerVariable::class),
            "amount" => new DummyVariable(NumberVariable::class),
            "cause" => new DummyVariable(NumberVariable::class),
        ];
    }
}