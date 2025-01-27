<?php

namespace aieuo\mineflow\trigger\event;

use pocketmine\event\player\PlayerJumpEvent;

class PlayerJumpEventTrigger extends PlayerEventTrigger {
    public function __construct() {
        parent::__construct("PlayerJumpEvent", PlayerJumpEvent::class);
    }
}