<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem;

use function in_array;

class FlowItemCategory {

    public const COMMON = "common";
    public const BLOCK = "block";
    public const COMMAND = "command";
    public const ENTITY = "entity";
    public const EVENT = "event";
    public const FORM = "form";
    public const INVENTORY = "inventory";
    public const ITEM = "item";
    public const WORLD = "world";
    public const PLAYER = "player";
    public const PLUGIN = "plugin";
    public const MATH = "math";
    public const STRING = "string";
    public const VARIABLE = "variable";
    public const SCRIPT = "script";
    public const CONFIG = "config";
    public const SCOREBOARD = "scoreboard";
    public const INTERNAL = "internal";

    /** @var string[]  */
    private static array $categories = [];

    public static function registerDefaults(): void {
        self::add(self::COMMON);
        self::add(self::PLAYER);
        self::add(self::ENTITY);
        self::add(self::INVENTORY);
        self::add(self::ITEM);
        self::add(self::COMMAND);
        self::add(self::BLOCK);
        self::add(self::WORLD);
        self::add(self::EVENT);
        self::add(self::SCRIPT);
        self::add(self::MATH);
        self::add(self::VARIABLE);
        self::add(self::CONFIG);
        self::add(self::STRING);
        self::add(self::FORM);
        self::add(self::SCOREBOARD);
        self::add(self::PLUGIN);
        self::add(self::INTERNAL);
    }

    public static function all(): array {
        return self::$categories;
    }

    public static function exists(string $category): bool {
        return in_array($category, self::$categories, true);
    }

    public static function add(string $category): bool {
        if (self::exists($category)) return false;

        self::$categories[] = $category;
        return true;
    }
}
