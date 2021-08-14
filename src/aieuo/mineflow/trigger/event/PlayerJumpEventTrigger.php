<?php

namespace aieuo\mineflow\trigger\event;

use pocketmine\event\player\PlayerJumpEvent;

class PlayerJumpEventTrigger extends PlayerEventTrigger {
    public function __construct(string $subKey = "") {
        parent::__construct(PlayerJumpEvent::class, $subKey);
    }
}