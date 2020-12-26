<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\economy\Economy;
use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Language;
use pocketmine\utils\TextFormat;

class AddMoney extends TypeMoney {

    protected $id = self::ADD_MONEY;

    protected $name = "action.addMoney.name";
    protected $detail = "action.addMoney.detail";

    public function execute(Recipe $origin): \Generator {
        $this->throwIfCannotExecute();

        if (!Economy::isPluginLoaded()) {
            throw new InvalidFlowValueException(TextFormat::RED.Language::get("economy.notfound"));
        }

        $name = $origin->replaceVariables($this->getPlayerName());
        $amount = $origin->replaceVariables($this->getAmount());

        $this->throwIfInvalidNumber($amount, 1);

        $economy = Economy::getPlugin();
        $economy->addMoney($name, (int)$amount);
        yield true;
    }
}