<?php

namespace aieuo\mineflow\trigger;

use aieuo\mineflow\event\EntityAttackEvent;
use aieuo\mineflow\event\ServerStartEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
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

class EventTriggers {

    /** @var array */
    private static $events = [
        "PlayerChatEvent" => [PlayerChatEvent::class, "trigger.event.PlayerChatEvent"],
        "PlayerCommandPreprocessEvent" => [PlayerCommandPreprocessEvent::class, "trigger.event.PlayerCommandPreprocessEvent"],
        "PlayerInteractEvent" => [PlayerInteractEvent::class, "trigger.event.PlayerInteractEvent"],
        "PlayerJoinEvent" => [PlayerJoinEvent::class, "trigger.event.PlayerJoinEvent"],
        "PlayerQuitEvent" => [PlayerQuitEvent::class, "trigger.event.PlayerQuitEvent"],
        "BlockBreakEvent" => [BlockBreakEvent::class, "trigger.event.BlockBreakEvent"],
        "BlockPlaceEvent" => [BlockPlaceEvent::class, "trigger.event.BlockPlaceEvent"],
        "ServerStartEvent" => [ServerStartEvent::class, "trigger.event.ServerStartEvent"],
        "SignChangeEvent" => [SignChangeEvent::class, "trigger.event.SignChangeEvent"],
        "EntityDamageEvent" => [EntityDamageEvent::class, "trigger.event.EntityDamageEvent"],
        "EntityAttackEvent" => [EntityAttackEvent::class, "trigger.event.EntityAttackEvent"],
        "PlayerToggleFlightEvent" => [PlayerToggleFlightEvent::class, "trigger.event.PlayerToggleFlightEvent"],
        "PlayerDeathEvent" => [PlayerDeathEvent::class, "trigger.event.PlayerDeathEvent"],
        "EntityLevelChangeEvent" => [EntityLevelChangeEvent::class, "trigger.event.EntityLevelChangeEvent"],
        "CraftItemEvent" => [CraftItemEvent::class, "trigger.event.CraftItemEvent"],
        "PlayerDropItemEvent" => [PlayerDropItemEvent::class, "trigger.event.PlayerDropItemEvent"],
        "FurnaceBurnEvent" => [FurnaceBurnEvent::class, "trigger.event.FurnaceBurnEvent"],
        "LevelLoadEvent" => [LevelLoadEvent::class, "trigger.event.LevelLoadEvent"],
        "PlayerBedEnterEvent" => [PlayerBedEnterEvent::class, "trigger.event.PlayerBedEnterEvent"],
        "PlayerChangeSkinEvent" => [PlayerChangeSkinEvent::class, "trigger.event.PlayerChangeSkinEvent"],
        "PlayerExhaustEvent" => [PlayerExhaustEvent::class, "trigger.event.PlayerExhaustEvent"],
        "PlayerItemConsumeEvent" => [PlayerItemConsumeEvent::class, "trigger.event.PlayerItemConsumeEvent"],
        "PlayerMoveEvent" => [PlayerMoveEvent::class, "trigger.event.PlayerMoveEvent"],
        "PlayerToggleSneakEvent" => [PlayerToggleSneakEvent::class, "trigger.event.PlayerToggleSneakEvent"],
        "PlayerToggleSprintEvent" => [PlayerToggleSprintEvent::class, "trigger.event.PlayerToggleSprintEvent"],
        "ProjectileHitEntityEvent" => [ProjectileHitEntityEvent::class, "trigger.event.ProjectileHitEntityEvent"],
    ];

    /** @var array */
    private static $defaultEnableEvents = [
        "PlayerChatEvent",
        "PlayerCommandPreprocessEvent",
        "PlayerInteractEvent",
        "PlayerJoinEvent",
        "BlockBreakEvent",
        "BlockPlaceEvent",
        "ServerStartEvent",
        "EntityDamageEvent",
        "EntityAttackEvent",
        "PlayerToggleFlightEvent",
        "PlayerDeathEvent",
        "EntityLevelChangeEvent",
        "CraftItemEvent",
    ];

    public static function getEvents(): array {
        return self::$events;
    }

    public static function getDefaultEventSettings(): array {
        $events = self::getEvents();
        $enables = self::$defaultEnableEvents;
        $settings = [];

        foreach ($events as $event => $value) {
            $settings[$event] = in_array($event, $enables);
        }
        return $settings;
    }

    public static function getEventPath(string $event): ?string {
        return self::getEvents()[$event][0] ?? null;
    }
}