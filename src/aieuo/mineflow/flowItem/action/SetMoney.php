<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\economy\Economy;
use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Language;
use pocketmine\utils\TextFormat;

class SetMoney extends TypeMoney {

    protected $id = self::SET_MONEY;

    protected $name = "action.setMoney.name";
    protected $detail = "action.setMoney.detail";

    public function execute(Recipe $source): \Generator {
        $this->throwIfCannotExecute();

        if (!Economy::isPluginLoaded()) {
            throw new InvalidFlowValueException(TextFormat::RED.Language::get("economy.notfound"));
        }

        $name = $source->replaceVariables($this->getPlayerName());
        $amount = $source->replaceVariables($this->getAmount());

        $this->throwIfInvalidNumber($amount, 0);

        $economy = Economy::getPlugin();
        $economy->setMoney($name, (int)$amount);
        yield true;
    }
}