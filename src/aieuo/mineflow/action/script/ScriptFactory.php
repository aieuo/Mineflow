<?php

namespace aieuo\mineflow\action\script;

class ScriptFactory {
    private static $list = [];

    public static function init(): void {
    }

    /**
     * @param  string $id
     * @return Script|null
     */
    public static function get(string $id): ?Script {
        if (isset(self::$list[$id])) {
            return clone self::$list[$id];
        }
        return null;
    }

    /**
     * @return Script[]
     */
    public static function getByCategory(int $category): array {
        $scripts = [];
        foreach (self::$list as $script) {
            if ($script->getCategory() === $category) $scripts[] = $script;
        }
        return $scripts;
    }

    /**
     * @return Script[]
     */
    public static function getAll(): array {
        return self::$list;
    }

    /**
     * @param  Script $script
     */
    public static function register(Script $script): void {
        self::$list[$script->getId()] = clone $script;
    }
}