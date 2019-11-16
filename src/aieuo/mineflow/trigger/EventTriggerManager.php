<?php

namespace aieuo\mineflow\trigger;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\inventory\FurnaceBurnEvent;
use pocketmine\event\level\LevelLoadEvent;
use pocketmine\event\player\PlayerBedEnterEvent;
use pocketmine\event\player\PlayerChangeSkinEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerToggleFlightEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\event\player\PlayerToggleSprintEvent;
use pocketmine\event\server\LowMemoryEvent;

class EventTriggerManager extends TriggerManager {

    /** @var array */
    private $events = [ // TODO: 名前
        "PlayerChatEvent" => [PlayerChatEvent::class, ""],
        "PlayerCommandPreprocessEvent" => [PlayerCommandPreprocessEvent::class, ""],
        "PlayerInteractEvent" => [PlayerInteractEvent::class, ""],
        "PlayerJoinEvent" => [PlayerJoinEvent::class, ""],
        "PlayerQuitEvent" => [PlayerQuitEvent::class, ""],
        "BlockBreakEvent" => [BlockBreakEvent::class, ""],
        "BlockPlaceEvent" => [BlockPlaceEvent::class, ""],
        "SignChangeEvent" => [SignChangeEvent::class, ""],
        "EntityDamageEvent" => [EntityDamageEvent::class, ""],
        // "EntityAttackEvent" => [, ""],
        "PlayerToggleFlightEvent" => [PlayerToggleFlightEvent::class, ""],
        "PlayerDeathEvent" => [PlayerDeathEvent::class, ""],
        "EntityLevelChangeEvent" => [EntityLevelChangeEvent::class, ""],
        "CraftItemEvent" => [CraftItemEvent::class, ""],
        "PlayerDropItemEvent" => [PlayerDropItemEvent::class, ""],
        "FurnaceBurnEvent" => [FurnaceBurnEvent::class, ""],
        "LevelLoadEvent" => [LevelLoadEvent::class, ""],
        "PlayerBedEnterEvent" => [PlayerBedEnterEvent::class, ""],
        "PlayerChangeSkinEvent" => [PlayerChangeSkinEvent::class, ""],
        "PlayerExhaustEvent" => [PlayerExhaustEvent::class, ""],
        "PlayerItemConsumeEvent" => [PlayerItemConsumeEvent::class, ""],
        "PlayerMoveEvent" => [PlayerMoveEvent::class, ""],
        "PlayerToggleSneakEvent" => [PlayerToggleSneakEvent::class, ""],
        "PlayerToggleSprintEvent" => [PlayerToggleSprintEvent::class, ""],
        "LowMemoryEvent" => [LowMemoryEvent::class, ""],
        "ProjectileHitEntityEvent" => [ProjectileHitEntityEvent::class, ""],
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

    public function getEventPath(string $event): ?string {
        return $this->getEvents[$event][0] ?? null;
    }
}