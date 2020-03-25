<?php

namespace aieuo\mineflow\flowItem\action;

use pocketmine\utils\TextFormat;
use pocketmine\entity\Entity;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\economy\Economy;

class TakeMoney extends TypeMoney {

    protected $id = self::TAKE_MONEY;

    protected $name = "action.takeMoney.name";
    protected $detail = "action.takeMoney.detail";

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;

    public function execute(?Entity $target, Recipe $origin): ?bool {
        if (!$this->canExecute($target)) return null;

        if (!Economy::isPluginLoaded()) {
            $target->sendMessage(TextFormat::RED.Language::get("economy.notfound"));
            return null;
        }

        $amount = $origin->replaceVariables($this->getAmount());

        if (!$this->checkValidNumberDataAndAlert($amount, 1, null, $target)) return null;

        $economy = Economy::getPlugin();
        $economy->takeMoney($target->getName(), (int)$amount);
        return true;
    }
}