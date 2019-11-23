<?php

namespace aieuo\mineflow\condition;

use pocketmine\utils\TextFormat;
use pocketmine\entity\Entity;
use pocketmine\Player;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\economy\Economy;
use aieuo\mineflow\condition\TypeMoney;

class OverMoney extends TypeMoney {

    protected $id = self::OVER_MONEY;

    protected $name = "@condition.overMoney.name";
    protected $description = "@condition.overMoney.description";
    protected $detail = "condition.overMoney.detail";

    public function execute(?Entity $target, ?Recipe $origin = null): ?bool {
        if (!($target instanceof Player)) return null;

        if (!$this->isDataValid()) {
            $target->sendMessage(Language::get("invalid.contents", [$this->getName()]));
            return null;
        }
        if (!Economy::isPluginLoaded()) {
            $target->sendMessage(TextFormat::RED.Language::get("economy.notfound"));
            return null;
        }

        $amount = $this->getAmount();
        if ($origin instanceof Recipe) {
            $amount = $origin->replaceVariables($amount);
        }

        if (!is_numeric($amount)) {
            $target->sendMessage(Language::get("condition.error", [$this->getName(), Language::get("mineflow.contents.notNumber")]));
            return null;
        }

        $mymoney = Economy::getPlugin()->getMoney($target->getName());
        return $mymoney >= (int)$amount;
    }
}