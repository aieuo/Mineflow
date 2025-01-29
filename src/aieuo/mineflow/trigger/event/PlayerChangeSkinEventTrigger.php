<?php

namespace aieuo\mineflow\trigger\event;

use pocketmine\event\player\PlayerChangeSkinEvent;

class PlayerChangeSkinEventTrigger extends PlayerEventTrigger {
    public function __construct() {
        parent::__construct("PlayerChangeSkinEvent", PlayerChangeSkinEvent::class);
    }
}