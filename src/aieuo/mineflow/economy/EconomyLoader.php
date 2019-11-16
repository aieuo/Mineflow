<?php

namespace aieuo\mineflow\economy;

interface EconomyLoader {
    /**
     * 所持金を取得する
     * @param  string $name プレイヤーの名前
     * @return int          所持金
     */
    public function getMoney(string $name);

    /**
     * 所持金を増やす
     * @param string $name  プレイヤーの名前
     * @param int    $money 増やす額
     */
    public function addMoney(string $name, int $money);

    /**
     * 所持金を減らす
     * @param  string $name  プレイヤーの名前
     * @param  int    $money 減らす額
     */
    public function takeMoney(string $name, int $money);

    /**
     * 所持金を設定する
     *
     * @param string $name
     * @param integer $money
     * @return void
     */
    public function setMoney(string $name, int $money);
}