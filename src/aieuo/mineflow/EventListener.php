<?php

namespace aieuo\mineflow;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\Listener;
use aieuo\mineflow\utils\Session;
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

    public function onJoin(PlayerJoinEvent $event) {
        Session::createSession($event->getPlayer());
    }

    public function onQuit(PlayerQuitEvent $event) {
        Session::destroySession($event->getPlayer());
    }
}