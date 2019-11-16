<?php

namespace aieuo\mineflow\utils;

class Categories {
    // TODO 言語
    const CATEGORY_ACTION_COMMON = 0;
    const CATEGORY_ACTION_SCRIPT = 1;
    const CATEGRY_ACTION_MESSAGE = 2;

    const CATEGORY_CONDITION_COMMON = 1000;
    const CATEGORY_CONDITION_SCRIPT = 1001;
    const CATEGORY_CONDITION_MONEY = 1002;
    const CATEGORY_CONDITION_ITEM = 1003;

    public static function getActionCategories(): array {
        return [
            self::CATEGORY_ACTION_COMMON => "common",
            self::CATEGORY_ACTION_SCRIPT => "script",
            self::CATEGRY_ACTION_MESSAGE => "message",
        ];
    }

    public static function getConditionCategories(): array {
        return [
            self::CATEGORY_CONDITION_COMMON => "common",
            self::CATEGORY_CONDITION_SCRIPT => "script",
            self::CATEGORY_CONDITION_MONEY => "money",
            self::CATEGORY_CONDITION_ITEM => "item",
        ];
    }
}