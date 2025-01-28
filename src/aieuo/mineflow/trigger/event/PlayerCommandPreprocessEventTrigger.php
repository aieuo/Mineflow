<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\object\PlayerVariable;
use aieuo\mineflow\variable\StringVariable;
use pocketmine\event\Event;
use pocketmine\event\server\CommandEvent;
use pocketmine\player\Player;

class PlayerCommandPreprocessEventTrigger extends EventTrigger {
    public function __construct() {
        parent::__construct("PlayerCommandPreprocessEvent", CommandEvent::class);
    }

    public function filter(Event $event): bool {
        /** @var CommandEvent $event */
        return $event->getSender() instanceof Player;
    }

    public function getVariables(mixed $event): array {
        /** @var CommandEvent $event */
        /** @var Player $target */
        $target = $event->getSender();
        $variables = array_merge(DefaultVariables::getCommandVariables($event->getCommand()), DefaultVariables::getPlayerVariables($target));
        $variables["message"] = new StringVariable($event->getCommand());
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