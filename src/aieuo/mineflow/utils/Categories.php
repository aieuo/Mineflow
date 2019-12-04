<?php

namespace aieuo\mineflow\utils;

class Categories {
    // TODO 言語
    const CATEGORY_ACTION_COMMON = 0;
    const CATEGORY_ACTION_SCRIPT = 1;
    const CATEGORY_ACTION_MESSAGE = 2;
    const CATEGORY_ACTION_ENTITY = 3;
    const CATEGORY_ACTION_PLAYER = 4;
    const CATEGORY_ACTION_MONEY = 5;
    const CATEGORY_ACTION_CALCULATION = 6;
    const CATEGORY_ACTION_VARIABLE = 7;

    const CATEGORY_CONDITION_COMMON = 1000;
    const CATEGORY_CONDITION_SCRIPT = 1001;
    const CATEGORY_CONDITION_MONEY = 1002;
    const CATEGORY_CONDITION_ITEM = 1003;
    const CATEGORY_CONDITION_ENTITY = 1004;
    const CATEGORY_CONDITION_VARIABLE = 1005;

    public static function getActionCategories(): array {
        return [
            self::CATEGORY_ACTION_COMMON => "common",
            self::CATEGORY_ACTION_MESSAGE => "message",
            self::CATEGORY_ACTION_ENTITY => "entity",
            self::CATEGORY_ACTION_PLAYER => "player",
            self::CATEGORY_ACTION_SCRIPT => "script",
            self::CATEGORY_ACTION_MONEY => "money",
            self::CATEGORY_ACTION_CALCULATION => "calculation",
            self::CATEGORY_ACTION_VARIABLE => "variable",
        ];
    }

    public static function getConditionCategories(): array {
        return [
            self::CATEGORY_CONDITION_COMMON => "common",
            self::CATEGORY_CONDITION_SCRIPT => "script",
            self::CATEGORY_CONDITION_MONEY => "money",
            self::CATEGORY_CONDITION_ITEM => "item",
            self::CATEGORY_CONDITION_ENTITY => "entity",
            self::CATEGORY_CONDITION_VARIABLE => "variable",
        ];
    }
}