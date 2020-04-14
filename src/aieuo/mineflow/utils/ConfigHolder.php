<?php

namespace aieuo\mineflow\utils;

use aieuo\mineflow\Main;
use pocketmine\utils\Config;

class ConfigHolder {

    /** @var Config[] */
    private static $configs = [];

    public static function getConfig(string $name): Config {
        $name = preg_replace("#[.¥/:?<>|*\"]#", "", preg_quote($name));
        if (isset(self::$configs[$name])) return self::$configs[$name];

        $dir = Main::getInstance()->getDataFolder()."configs/";
        if (!file_exists($dir)) @mkdir($dir, 0777, true);

        self::$configs[$name] = new Config($dir.$name.".yml", Config::YAML);
        return self::$configs[$name];
    }
}