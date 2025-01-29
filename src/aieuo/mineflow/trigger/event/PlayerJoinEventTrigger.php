<?php

namespace aieuo\mineflow\trigger\event;

use pocketmine\event\player\PlayerJoinEvent;

class PlayerJoinEventTrigger extends PlayerEventTrigger {
    public function __construct() {
        parent::__construct("PlayerJoinEvent", PlayerJoinEvent::class);
    }
}