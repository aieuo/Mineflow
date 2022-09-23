<?php
declare(strict_types=1);


namespace aieuo\mineflow;

class Mineflow {

    private static string $pluginVersion;

    public static function init(Main $main): void {
        self::$pluginVersion = $main->getDescription()->getVersion();
    }

    public static function pluginVersion(): string {
        return self::$pluginVersion;
    }
}