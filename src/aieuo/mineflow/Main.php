<?php

namespace aieuo\mineflow;

use pocketmine\utils\Config;
use pocketmine\plugin\PluginBase;
use aieuo\mineflow\utils\Language;

class Main extends PluginBase {

    /** @var Main */
    private static $instance;

    /** @var Config */
    private $config;

    /** @var bool */
    private $loaded = false;

    public static function getInstance(): ?self {
        return self::$instance;
    }

    public function onEnable() {
        self::$instance = $this;

        $serverLanguage = $this->getServer()->getLanguage()->getLang();
        $this->config = new Config($this->getDataFolder()."config.yml", Config::YAML, [
            "language" => $serverLanguage,
        ]);
        $this->config->save();

        Language::setLanguage($this->config->get("language", "eng"));
        if (!Language::loadMessage()) {
            foreach (Language::getLoadErrorMessage($serverLanguage) as $error) {
                $this->getLogger()->warning($error);
            }
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }

        $loaded = true;
    }

    public function onDisable() {
        if (!$this->loaded) return;
    }

    public function getConfig(): Config {
        return $this->config;
    }
}