<?php
declare(strict_types=1);


namespace aieuo\mineflow;

class Mineflow {

    private static string $pluginVersion;

    private static bool $debug = false;

    public static function init(Main $main): void {
        self::$pluginVersion = $main->getDescription()->getVersion();
    }

    public static function getPluginVersion(): string {
        return self::$pluginVersion;
    }

    public static function isDebug(): bool {
        return self::$debug;
    }

    public static function setDebug(bool $debug): void {
        self::$debug = $debug;
    }
}
