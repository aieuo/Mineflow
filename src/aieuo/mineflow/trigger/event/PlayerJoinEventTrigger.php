<?php

namespace aieuo\mineflow\trigger\event;

use pocketmine\event\player\PlayerJoinEvent;

class PlayerJoinEventTrigger extends PlayerEventTrigger {
    public function __construct(string $subKey = "") {
        parent::__construct("PlayerJoinEvent", $subKey, PlayerJoinEvent::class);
    }
}