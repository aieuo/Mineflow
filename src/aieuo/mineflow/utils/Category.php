<?php

namespace aieuo\mineflow\utils;

class Category {

    public const COMMON = "common";
    public const BLOCK = "block";
    public const COMMAND = "command";
    public const ENTITY = "entity";
    public const EVENT = "event";
    public const FORM = "form";
    public const INVENTORY = "inventory";
    public const ITEM = "item";
    public const LEVEL = "level";
    public const PLAYER = "player";
    public const PLUGIN = "plugin";
    public const MATH = "math";
    public const STRING = "string";
    public const VARIABLE = "variable";
    public const SCRIPT = "script";
    public const SCOREBOARD = "scoreboard";

    /** @var string[]  */
    private static $categories = [
        self::COMMON,
        self::PLAYER,
        self::ENTITY,
        self::INVENTORY,
        self::ITEM,
        self::COMMAND,
        self::BLOCK,
        self::LEVEL,
        self::EVENT,
        self::SCRIPT,
        self::MATH,
        self::VARIABLE,
        self::STRING,
        self::FORM,
        self::SCOREBOARD,
        self::PLUGIN,
    ];

    public static function getCategories(): array {
        return self::$categories;
    }

    public static function existsCategory(string $category): bool {
        return in_array($category, self::$categories, true);
    }

    public static function addCategory(string $category): bool {
        if (self::existsCategory($category)) return false;

        self::$categories[] = $category;
        return true;
    }
}