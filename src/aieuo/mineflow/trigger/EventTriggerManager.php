<?php

namespace aieuo\mineflow\trigger;

class EventTriggerManager extends TriggerManager {

    /** @var array */
    private $events = [ // TODO: 名前
        "PlayerChatEvent" => "",
        "PlayerCommandPreprocessEvent" => "",
        "PlayerInteractEvent" => "",
        "PlayerJoinEvent" => "",
        "PlayerQuitEvent" => "",
        "BlockBreakEvent" => "",
        "BlockPlaceEvent" => "",
        "EntityDamageEvent" => "",
        "EntityAttackEvent" => "",
        "PlayerToggleFlightEvent" => "",
        "PlayerDeathEvent" => "",
        "EntityLevelChangeEvent" => "",
        "CraftItemEvent" => "",
        "PlayerDropItemEvent" => "",
    ];

    /** @var array */
    private $defaultEnableEvents = [
        "PlayerChatEvent",
        "PlayerCommandPreprocessEvent",
        "PlayerInteractEvent",
        "PlayerJoinEvent",
        "BlockBreakEvent",
        "BlockPlaceEvent",
        "EntityDamageEvent",
        "EntityAttackEvent",
        "PlayerToggleFlightEvent",
        "PlayerDeathEvent",
        "EntityLevelChangeEvent",
        "CraftItemEvent",
    ];

    public function getEvents(): array {
        return $this->events;
    }

    public function getDefaultEventSettings(): array {
        $events = $this->getEvents();
        $enables = $this->defaultEnableEvents;
        $settings = [];

        foreach ($events as $event => $value) {
            $settings[$event] = in_array($event, $enables);
        }
        return $settings;
    }
}