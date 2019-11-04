<?php

namespace aieuo\mineflow;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;

class EventListener implements Listener {

    /** @var Main */
    private $owner;

    public function __construct(Main $owner) {
        $this->owner = $owner;
    }

    private function getOwner(): Main {
        return $this->owner;
    }
}