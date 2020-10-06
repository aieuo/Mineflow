<?php

namespace aieuo\mineflow\trigger\event;

use pocketmine\event\player\PlayerQuitEvent;

class PlayerQuitEventTrigger extends PlayerEventTrigger {
    public function __construct(string $subKey = "") {
        parent::__construct(PlayerQuitEvent::class, $subKey);
    }
}