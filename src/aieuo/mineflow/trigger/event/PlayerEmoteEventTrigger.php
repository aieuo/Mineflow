<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\PlayerVariable;
use aieuo\mineflow\variable\StringVariable;
use pocketmine\event\player\PlayerEmoteEvent;

class PlayerEmoteEventTrigger extends EventTrigger {
    public function __construct() {
        parent::__construct("PlayerEmoteEvent", PlayerEmoteEvent::class);
    }

    public function getVariables(mixed $event): array {
        /** @var PlayerEmoteEvent $event */
        $target = $event->getPlayer();
        $emoteId = $event->getEmoteId();
        return array_merge(DefaultVariables::getPlayerVariables($target), [
            "emoteId" => new StringVariable($emoteId),
        ]);
    }

    public function getVariablesDummy(): array {
        return [
            "target" => new DummyVariable(PlayerVariable::class),
            "emoteId" => new DummyVariable(StringVariable::class),
        ];
    }
}