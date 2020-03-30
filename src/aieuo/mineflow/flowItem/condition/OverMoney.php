<?php

namespace aieuo\mineflow\flowItem\condition;

use pocketmine\utils\TextFormat;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\economy\Economy;

class OverMoney extends TypeMoney {

    protected $id = self::OVER_MONEY;

    protected $name = "condition.overMoney.name";
    protected $detail = "condition.overMoney.detail";

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;
    protected $returnValueType = self::RETURN_NONE;

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        if (!Economy::isPluginLoaded()) {
            throw new \UnexpectedValueException(TextFormat::RED.Language::get("economy.notfound"));
        }

        $name = $origin->replaceVariables($this->getPlayerName());
        $amount = $origin->replaceVariables($this->getAmount());

        $this->throwIfInvalidNumber($amount);

        $myMoney = Economy::getPlugin()->getMoney($name);
        return $myMoney >= (int)$amount;
    }
}