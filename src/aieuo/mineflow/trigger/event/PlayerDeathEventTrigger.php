<?php

namespace aieuo\mineflow\trigger\event;

use pocketmine\event\player\PlayerDeathEvent;

class PlayerDeathEventTrigger extends PlayerEventTrigger {
    public function __construct(string $subKey = "") {
        parent::__construct(PlayerDeathEvent::class, $subKey);
    }
}