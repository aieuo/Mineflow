<?php

namespace aieuo\mineflow\trigger\event;


class EventTriggerList {
    /** @var EventTrigger[][] */
    private static $list = [];

    public static function init(): void {
        self::add(new BlockBreakEventTrigger());
        self::add(new BlockPlaceEventTrigger());
        self::add(new CraftItemEventTrigger());
        self::add(new EntityAttackEventTrigger());
        self::add(new EntityDamageEventTrigger());
        self::add(new EntityLevelChangeEventTrigger());
        self::add(new FurnaceBurnEventTrigger());
        self::add(new LevelLoadEventTrigger());
        self::add(new PlayerBedEnterEventTrigger());
        self::add(new PlayerChatEventTrigger());
        self::add(new PlayerCommandPreprocessEventTrigger());
        self::add(new PlayerDeathEventTrigger());
        self::add(new PlayerDropItemEventTrigger());
        self::add(new PlayerExhaustEventTrigger());
        self::add(new PlayerInteractEventTrigger());
        self::add(new PlayerItemConsumeEventTrigger());
        self::add(new PlayerJoinEventTrigger());
        self::add(new PlayerMoveEventTrigger());
        self::add(new PlayerQuitEventTrigger());
        self::add(new PlayerToggleFlightEventTrigger());
        self::add(new PlayerToggleSneakEventTrigger());
        self::add(new PlayerToggleSprintEventTrigger());
        self::add(new ProjectileHitEntityEventTrigger());
        self::add(new SignChangeEventTrigger());
        self::add(new ServerStartEventTrigger());
    }

    public static function add(EventTrigger $trigger): void {
        self::$list[$trigger->getKey()][$trigger->getSubKey()] = $trigger;
    }

    public static function get(string $key, string $subKey = ""): ?EventTrigger {
        return self::$list[$key][$subKey] ?? null;
    }

    public static function exists(string $key, string $subKey = ""): bool {
        return isset(self::$list[$key][$subKey]);
    }

}