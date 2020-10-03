<?php

namespace aieuo\mineflow\economy;

class EconomyAPILoader implements EconomyLoader {

    private $plugin;

    public function __construct($plugin) {
        $this->plugin = $plugin;
    }

    public function getPlugin() {
        return $this->plugin;
    }

    public function getMoney(string $name): int {
        return (int)$this->getPlugin()->mymoney($name);
    }

    public function addMoney(string $name, int $money): void {
        $this->getPlugin()->addMoney($name, $money);
    }

    public function takeMoney(string $name, int $money): void {
        $this->getPlugin()->reduceMoney($name, $money);
    }

    public function setMoney(string $name, int $money): void {
        $this->getPlugin()->setMoney($name, $money);
    }
}