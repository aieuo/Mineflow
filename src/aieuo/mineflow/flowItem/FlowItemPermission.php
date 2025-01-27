<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem;

use function in_array;

class FlowItemPermission {

    public const LOOP = "loop";
    public const CHEAT = "cheat";
    public const CONSOLE = "console";
    public const CONFIG = "config";
    public const PERMISSION = "permission";

    /** @var string[]  */
    private static array $permissions = [];

    public static function registerDefaults(): void {
        self::add(self::LOOP);
        self::add(self::CHEAT);
        self::add(self::CONSOLE);
        self::add(self::CONFIG);
        self::add(self::PERMISSION);
    }

    public static function all(): array {
        return self::$permissions;
    }

    public static function exists(string $permission): bool {
        return in_array($permission, self::$permissions, true);
    }

    public static function add(string $permission): bool {
        if (self::exists($permission)) return false;

        self::$permissions[] = $permission;
        return true;
    }
}