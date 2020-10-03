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


    public static function getCategories(): array {
        return [
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
    }
}