<?php

namespace aieuo\mineflow\action\process;

use pocketmine\utils\TextFormat;
use pocketmine\entity\Entity;
use pocketmine\Player;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\economy\Economy;
use aieuo\mineflow\action\process\TypeMoney;

class SetMoney extends TypeMoney {

    protected $id = self::SET_MONEY;

    protected $name = "@action.setMoney.name";
    protected $description = "@action.setMoney.description";
    protected $detail = "action.setMoney.detail";

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;

    public function execute(?Entity $target, ?Recipe $origin = null): ?bool {
        if (!($target instanceof Player)) return false;

        if (!$this->isDataValid()) {
            $target->sendMessage(Language::get("invalid.contents", [$this->getName()]));
            return false;
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
            $target->sendMessage(Language::get("action.error", [$this->getName(), Language::get("action.money.notNumber")]));
            return null;
        } elseif ((int)$amount <= 0) {
            $target->sendMessage(Language::get("action.error", [$this->getName(), Language::get("action.money.zero")]));
            return null;
        }

        $economy = Economy::getPlugin();
        $economy->setMoney($target->getName(), (int)$amount);
        return true;
    }
}