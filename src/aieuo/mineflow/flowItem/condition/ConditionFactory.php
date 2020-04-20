<?php

namespace aieuo\mineflow\flowItem\condition;

class ConditionFactory {

    /** @var Condition[] */
    private static $list = [];

    public static function init() {
        /* common */
        self::register(new CheckNothing);
        self::register(new IsOp);
        self::register(new IsSneaking);
        self::register(new IsFlying);
        self::register(new RandomNumber);
        /* money */
        self::register(new OverMoney);
        self::register(new LessMoney);
        self::register(new TakeMoney);
        /* item */
        self::register(new InHand);
        self::register(new ExistsItem);
        self::register(new CanAddItem);
        self::register(new RemoveItem);
        /* script */
        self::register(new ComparisonNumber);
        self::register(new ComparisonString);
        self::register(new AndScript);
        self::register(new ORScript);
        self::register(new NotScript);
        self::register(new NorScript);
        self::register(new NandScript);
        self::register(new ExistsConfigFile);
        self::register(new ExistsConfigData);
        /* entity */
        self::register(new IsActiveEntity);
        self::register(new IsPlayer);
        self::register(new IsCreature);
        self::register(new InArea);
        /* player */
        self::register(new Gamemode);
        self::register(new HasPermission);
        /* variable */
        self::register(new ExistsVariable);
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
     * @param string $category
     * @param int|null $permission
     * @return Condition[]
     */
    public static function getByFilter(string $category = null, int $permission = null): array {
        $conditions = [];
        foreach (self::$list as $condition) {
            if ($category !== null and $condition->getCategory() !== $category) continue;
            if ($permission !== null and $condition->getPermission() > $permission) continue;
            $conditions[] = $condition;
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