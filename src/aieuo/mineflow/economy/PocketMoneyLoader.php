<?php

namespace aieuo\mineflow\economy;

class PocketMoneyLoader implements EconomyLoader {

    private $plugin;

    public function __construct($plugin) {
        $this->plugin = $plugin;
    }

    public function getPlugin() {
        return $this->plugin;
    }

    public function getMoney(string $name): int {
        return (int)$this->getPlugin()->getMoney($name);
    }

    public function addMoney(string $name, int $money): void {
        $mymoney = $this->getMoney($name);
        $this->getPlugin()->setMoney($name, $mymoney + $money);
    }

    public function takeMoney(string $name, int $money): void {
        $mymoney = $this->getMoney($name);
        $this->getPlugin()->setMoney($name, $mymoney - $money);
    }

    public function setMoney(string $name, int $money): void {
        $this->getPlugin()->setMoney($name, $money);
    }
}