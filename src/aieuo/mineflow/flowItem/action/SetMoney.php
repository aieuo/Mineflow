<?php

namespace aieuo\mineflow\flowItem\action;

use pocketmine\utils\TextFormat;
use pocketmine\entity\Entity;
use pocketmine\Player;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\economy\Economy;

class SetMoney extends TypeMoney {

    protected $id = self::SET_MONEY;

    protected $name = "action.setMoney.name";
    protected $detail = "action.setMoney.detail";

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;

    public function execute(?Entity $target, Recipe $origin): ?bool {
        if (!$this->canExecute($target)) return null;

        if (!Economy::isPluginLoaded()) {
            $target->sendMessage(TextFormat::RED.Language::get("economy.notfound"));
            return null;
        }

        $amount = $origin->replaceVariables($this->getAmount());

        if (!$this->checkValidNumberDataAndAlert($amount, 0, null, $target)) return null;

        $economy = Economy::getPlugin();
        $economy->setMoney($target->getName(), (int)$amount);
        return true;
    }
}