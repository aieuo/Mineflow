<?php

namespace aieuo\mineflow\economy;

class MoneySystemLoader implements EconomyLoader {

    private $plugin;

    public function __construct($plugin) {
        $this->plugin = $plugin;
    }

    public function getPlugin() {
        return $this->plugin;
    }

    public function getMoney(string $name) {
        return (int)$this->getPlugin()->getAPI()->get($name);
    }

    public function addMoney(string $name, int $money) {
        $this->getPlugin()->getAPI()->increase($name, $money);
        return true;
    }

    public function takeMoney(string $name, int $money) {
        $this->getPlugin()->getAPI()->reduce($name, $money);
        return true;
    }

    public function setMoney(string $name, int $money) {
        $this->getPlugin()->getAPI()->set($name, $money);
        return true;
    }
}