<?php

namespace aieuo\mineflow\flowItem\action;

use pocketmine\utils\TextFormat;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\economy\Economy;

class SetMoney extends TypeMoney {

    protected $id = self::SET_MONEY;

    protected $name = "action.setMoney.name";
    protected $detail = "action.setMoney.detail";

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        if (!Economy::isPluginLoaded()) {
            throw new \UnexpectedValueException(TextFormat::RED.Language::get("economy.notfound"));
        }

        $name = $origin->replaceVariables($this->getPlayerName());
        $amount = $origin->replaceVariables($this->getAmount());

        $this->throwIfInvalidNumber($amount, 0);

        $economy = Economy::getPlugin();
        $economy->setMoney($name, (int)$amount);
        return true;
    }
}