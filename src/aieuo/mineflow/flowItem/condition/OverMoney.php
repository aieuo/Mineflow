<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\economy\Economy;
use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Language;
use pocketmine\utils\TextFormat;

class OverMoney extends TypeMoney {

    protected $id = self::OVER_MONEY;

    protected $name = "condition.overMoney.name";
    protected $detail = "condition.overMoney.detail";

    public function execute(Recipe $origin): \Generator {
        $this->throwIfCannotExecute();

        if (!Economy::isPluginLoaded()) {
            throw new InvalidFlowValueException(TextFormat::RED.Language::get("economy.notfound"));
        }

        $name = $origin->replaceVariables($this->getPlayerName());
        $amount = $origin->replaceVariables($this->getAmount());

        $this->throwIfInvalidNumber($amount);

        $myMoney = Economy::getPlugin()->getMoney($name);

        yield true;
        return $myMoney >= (int)$amount;
    }
}