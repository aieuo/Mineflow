<?php

namespace aieuo\mineflow\condition;

class ConditionFactory {

    /** @var Condition[] */
    private static $list = [];

    public static function init() {
        /* common */
        self::register(new CheckNothing);
        self::register(new IsOp);
        self::register(new IsSneaking);
        self::register(new IsFlying);
        /* money */
        self::register(new OverMoney);
        self::register(new LessMoney);
        self::register(new TakeMoney);
        /* item */
        self::register(new InHand);
        self::register(new ExistsItem);
        self::register(new CanAddItem);
        /* variable */
        self::register(new ExistsListVariableKey);
    }

    /**
     * @param  string $id
     * @return Condition|null
     */
    public static function get(string $id): ?Condition {
        if (isset(self::$list[$id])) {
            return clone self::$list[$id];
        }
        return null;
    }

    /**
     * @return Condition[]
     */
    public static function getByCategory(int $category): array {
        $conditions = [];
        foreach (self::$list as $condition) {
            if ($condition->getCategory() === $category) $conditions[] = $condition;
        }
        return $conditions;
    }

    /**
     * @return array
     */
    public static function getAll(): array {
        return self::$list;
    }

    /**
     * @param  Condition $condition
     */
    public static function register(Condition $condition) {
        self::$list[$condition->getId()] = clone $condition;
    }
}