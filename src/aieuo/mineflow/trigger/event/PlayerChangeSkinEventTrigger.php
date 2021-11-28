<?php

namespace aieuo\mineflow\trigger\event;

use pocketmine\event\player\PlayerChangeSkinEvent;

class PlayerChangeSkinEventTrigger extends PlayerEventTrigger {
    public function __construct(string $subKey = "") {
        parent::__construct("PlayerChangeSkinEvent", $subKey, PlayerChangeSkinEvent::class);
    }
}