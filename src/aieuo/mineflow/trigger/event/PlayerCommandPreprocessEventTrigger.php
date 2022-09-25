<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\object\PlayerVariable;
use aieuo\mineflow\variable\StringVariable;
use pocketmine\event\player\PlayerCommandPreprocessEvent;

class PlayerCommandPreprocessEventTrigger extends EventTrigger {
    public function __construct(string $subKey = "") {
        parent::__construct("PlayerCommandPreprocessEvent", $subKey, PlayerCommandPreprocessEvent::class);
    }

    public function getVariables(mixed $event): array {
        /** @var PlayerCommandPreprocessEvent $event */
        $target = $event->getPlayer();
        $variables = array_merge(DefaultVariables::getCommandVariables(substr($event->getMessage(), 1)), DefaultVariables::getPlayerVariables($target));
        $variables["message"] = new StringVariable($event->getMessage());
        return $variables;
    }

    public function getVariablesDummy(): array {
        return [
            "target" => new DummyVariable(PlayerVariable::class),
            "message" => new DummyVariable(StringVariable::class),
            "cmd" => new DummyVariable(StringVariable::class),
            "args" => new DummyVariable(ListVariable::class, StringVariable::getTypeName()),
        ];
    }
}
