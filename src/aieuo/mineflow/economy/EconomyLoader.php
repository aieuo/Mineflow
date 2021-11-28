<?php

namespace aieuo\mineflow\economy;

interface EconomyLoader {

    public function getMoney(string $name): int;

    public function addMoney(string $name, int $money): void;

    public function takeMoney(string $name, int $money): void;

    public function setMoney(string $name, int $money): void;
}