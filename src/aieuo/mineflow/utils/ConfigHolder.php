<?php

namespace aieuo\mineflow\utils;

use aieuo\mineflow\Main;
use pocketmine\utils\Config;

class ConfigHolder {

    /** @var Config[] */
    private static array $configs = [];

    public static function existsConfigFile(string $name): bool {
        $name = preg_replace("#[.¥/:?<>|*\"]#u", "", preg_quote($name, "/@#~"));
        if (isset(self::$configs[$name])) return true;

        $path = Main::getInstance()->getDataFolder()."configs/".$name.".yml";
        return file_exists($path);
    }

    public static function getConfig(string $name): Config {
        $name = preg_replace("#[.¥/:?<>|*\"]#u", "", preg_quote($name, "/@#~"));
        if (isset(self::$configs[$name])) return self::$configs[$name];

        $dir = Main::getInstance()->getDataFolder()."configs/";
        if (!file_exists($dir)) @mkdir($dir, 0777, true);

        self::$configs[$name] = new Config($dir.$name.".yml", Config::YAML);
        return self::$configs[$name];
    }

    public static function setConfig(string $name, array $data, bool $save = false): void {
        $name = preg_replace("#[.¥/:?<>|*\"]#u", "", preg_quote($name, "/@#~"));

        $dir = Main::getInstance()->getDataFolder()."configs/";
        if (!file_exists($dir)) @mkdir($dir, 0777, true);

        $config = new Config($dir.$name.".yml", Config::YAML);
        $config->setAll($data);
        if ($save) $config->save();
        self::$configs[$name] = $config;
    }
}