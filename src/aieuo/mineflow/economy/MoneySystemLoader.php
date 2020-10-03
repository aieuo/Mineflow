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

    public function getMoney(string $name): int {
        return (int)$this->getPlugin()->getAPI()->get($name);
    }

    public function addMoney(string $name, int $money): void {
        $this->getPlugin()->getAPI()->increase($name, $money);
    }

    public function takeMoney(string $name, int $money): void {
        $this->getPlugin()->getAPI()->reduce($name, $money);
    }

    public function setMoney(string $name, int $money): void {
        $this->getPlugin()->getAPI()->set($name, $money);
    }
}