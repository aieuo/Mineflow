<?php

namespace aieuo\mineflow\trigger\event;

use pocketmine\event\player\PlayerQuitEvent;

class PlayerQuitEventTrigger extends PlayerEventTrigger {
    public function __construct() {
        parent::__construct("PlayerQuitEvent", PlayerQuitEvent::class);
    }
}