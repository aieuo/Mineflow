<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\GameModeVariable;
use aieuo\mineflow\variable\object\PlayerVariable;
use pocketmine\event\player\PlayerGameModeChangeEvent;

class PlayerGameModeChangeEventTrigger extends EventTrigger {
    public function __construct() {
        parent::__construct("PlayerGameModeChangeEvent", PlayerGameModeChangeEvent::class);
    }

    public function getVariables(mixed $event): array {
        /** @var PlayerGameModeChangeEvent $event */
        $target = $event->getPlayer();
        $newGamemode = $event->getNewGamemode();
        return array_merge(DefaultVariables::getPlayerVariables($target), [
            "newGamemode" => new GameModeVariable($newGamemode),
        ]);
    }

    public function getVariablesDummy(): array {
        return [
            "target" => new DummyVariable(PlayerVariable::class),
            "newGamemode" => new DummyVariable(GameModeVariable::class),
        ];
    }
}