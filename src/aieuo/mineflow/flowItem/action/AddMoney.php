<?php

namespace aieuo\mineflow\flowItem\action;

use pocketmine\utils\TextFormat;
use pocketmine\entity\Entity;
use pocketmine\Player;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\economy\Economy;

class AddMoney extends TypeMoney {

    protected $id = self::ADD_MONEY;

    protected $name = "action.addMoney.name";
    protected $detail = "action.addMoney.detail";
    protected $detailDefaultReplace = ["money"];

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;

    public function execute(?Entity $target, Recipe $origin): ?bool {
        if (!$this->canExecute($target, true)) return null;

        if (!Economy::isPluginLoaded()) {
            $target->sendMessage(TextFormat::RED.Language::get("economy.notfound"));
            return null;
        }

        $amount = $origin->replaceVariables($this->getAmount());

        if (!$this->checkValidNumberDataAndAlert($amount, 1, null, $target)) return null;

        $economy = Economy::getPlugin();
        $economy->addMoney($target->getName(), (int)$amount);
        return true;
    }
}