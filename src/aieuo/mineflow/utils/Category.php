<?php

namespace aieuo\mineflow\utils;

class Category {

    const COMMON = "common";
    const BLOCK = "block";
    const COMMAND = "command";
    const ENTITY = "entity";
    const EVENT = "event";
    const FORM = "form";
    const INVENTORY = "inventory";
    const ITEM = "item";
    const LEVEL = "level";
    const PLAYER = "player";
    const PLUGIN = "plugin";
    const MATH = "math";
    const VARIABLE = "variable";
    const SCRIPT = "script";
    const SCOREBOARD = "scoreboard";


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
            self::FORM,
            self::SCOREBOARD,
            self::PLUGIN,
        ];
    }
}