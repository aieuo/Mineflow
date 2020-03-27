<?php

namespace aieuo\mineflow\economy;

use aieuo\mineflow\utils\Language;
use aieuo\mineflow\Main;

class Economy {
    /** @var EconomyLoader|null */
    private static $economy = null;
    /* @var Main */
    private $owner;

    public function __construct(Main $owner) {
        $this->owner = $owner;
    }

    public function loadPlugin() {
        $pluginManager = $this->owner->getServer()->getPluginManager();
        if (($plugin = $pluginManager->getPlugin("EconomyAPI")) !== null) {
            self::$economy = new EconomyAPILoader($plugin);
            $this->owner->getLogger()->info(Language::get("economy.found", ["EconomyAPI"]));
        } elseif (($plugin = $pluginManager->getPlugin("MoneySystem")) !== null) {
            self::$economy = new MoneySystemLoader($plugin);
            $this->owner->getLogger()->info(Language::get("economy.found", ["MoneySystem"]));
        } elseif (($plugin = $pluginManager->getPlugin("PocketMoney")) !== null) {
            self::$economy = new PocketMoneyLoader($plugin);
            $this->owner->getLogger()->info(Language::get("economy.found", ["PocketMoney"]));
        } else {
            $this->owner->getLogger()->warning(Language::get("economy.notfound"));
        }
    }

    public static function isPluginLoaded(): bool {
        return self::$economy !== null;
    }

    public static function getPlugin(): ?EconomyLoader {
        return self::$economy;
    }
}