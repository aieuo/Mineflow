<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\PlayerVariable;
use aieuo\mineflow\variable\StringVariable;
use pocketmine\event\player\PlayerChatEvent;

class PlayerChatEventTrigger extends EventTrigger {
    public function __construct() {
        parent::__construct("PlayerChatEvent", PlayerChatEvent::class);
    }

    public function getVariables(mixed $event): array {
        /** @var PlayerChatEvent $event */
        $target = $event->getPlayer();
        $variables =  DefaultVariables::getPlayerVariables($target);
        $variables["message"] = new StringVariable($event->getMessage());
        return $variables;
    }

    public function getVariablesDummy(): array {
        return [
            "target" => new DummyVariable(PlayerVariable::class),
            "message" => new DummyVariable(StringVariable::class),
        ];
    }
}