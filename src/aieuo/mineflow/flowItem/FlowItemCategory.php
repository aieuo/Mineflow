<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem;

use aieuo\mineflow\utils\Language;
use function in_array;

class FlowItemCategory {

    public const COMMON = "common";
    public const BLOCK = "block";
    public const COMMAND = "command";
    public const ENTITY = "entity";
    public const EVENT = "event";

    public const FORM = "form";
    public const FORM_BUTTON = "button";
    public const FORM_ELEMENT = "element";

    public const INVENTORY = "inventory";
    public const ITEM = "item";
    public const WORLD = "world";
    public const PLAYER = "player";
    public const PLAYER_MESSAGE = "message";
    public const EFFECT = "effect";
    public const BOSSBAR = "bossbar";
    public const PLAYER_PERMISSION = "player_permission";
    public const PLUGIN = "plugin";
    public const MATH = "math";
    public const STRING = "string";

    public const VARIABLE = "variable";

    public const PLAYER_DATA = "player_data";
    public const SCRIPT = "script";
    public const SCRIPT_IF = "if";
    public const SCRIPT_LOOP = "loop";
    public const CONFIG = "config";
    public const SCOREBOARD = "scoreboard";
    public const INTERNAL = "internal";

    public const PLAYER_CHAT_EVENT = "event.player_chat_event";

    /** @var string[]  */
    private static array $categories = [];
    /** @var array<string, ?string>  */
    private static array $parents = [];
    /** @var array<string, string[]>  */
    private static array $children = [];

    /** @var array<string, string> */
    private static array $names = [];

    public static function registerDefaults(): void {
        self::add(self::COMMON);
        self::add(self::PLAYER);
        self::add(self::PLAYER_MESSAGE, self::PLAYER);
        self::add(self::BOSSBAR, self::PLAYER);
        self::add(self::PLAYER_PERMISSION, self::PLAYER);
        self::add(self::ENTITY);
        self::add(self::EFFECT, self::ENTITY);
        self::add(self::INVENTORY);
        self::add(self::ITEM);
        self::add(self::COMMAND);
        self::add(self::BLOCK);
        self::add(self::WORLD);
        self::add(self::EVENT);
        self::add(self::PLAYER_CHAT_EVENT, self::EVENT, "trigger.event.PlayerChatEvent");
        self::add(self::SCRIPT);
        self::add(self::SCRIPT_IF, self::SCRIPT);
        self::add(self::SCRIPT_LOOP, self::SCRIPT);
        self::add(self::MATH);
        self::add(self::VARIABLE);
        self::add(self::PLAYER_DATA, self::VARIABLE);
        self::add(self::CONFIG);
        self::add(self::STRING);
        self::add(self::FORM);
        self::add(self::FORM_BUTTON, self::FORM);
        self::add(self::FORM_ELEMENT, self::FORM);
        self::add(self::SCOREBOARD);
        self::add(self::INTERNAL);
        self::add(self::PLUGIN);
    }

    public static function all(): array {
        return self::$categories;
    }

    public static function exists(string $category): bool {
        return in_array($category, self::$categories, true);
    }

    public static function add(string $category, string $parent = null, string $nameKey = null): bool {
        if (self::exists($category)) return false;

        self::$categories[] = $category;
        self::$parents[$category] = $parent;
        self::$children[$parent][] = $category;
        self::$names[$category] = $nameKey ?? "category.".$category;
        return true;
    }

    public static function parents(): array {
        return self::$parents;
    }

    public static function getParent(string $category): ?string {
        return self::$parents[$category] ?? null;
    }

    public static function allChildren(): array {
        return self::$children;
    }

    public static function getChildren(?string $category): array {
        return self::$children[$category] ?? [];
    }

    public static function name(string $category): ?string {
        if (!isset(self::$names[$category])) return null;
        return Language::get(self::$names[$category]);
    }

    public static function root(): array {
        return self::getChildren(null);
    }
}