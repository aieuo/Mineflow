<?php

namespace aieuo\mineflow\flowItem\action;

use pocketmine\utils\TextFormat;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\economy\Economy;

class TakeMoney extends TypeMoney {

    protected $id = self::TAKE_MONEY;

    protected $name = "action.takeMoney.name";
    protected $detail = "action.takeMoney.detail";

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;
    protected $returnValueType = self::RETURN_NONE;

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        if (!Economy::isPluginLoaded()) {
            throw new \UnexpectedValueException(TextFormat::RED.Language::get("economy.notfound"));
        }

        $name = $origin->replaceVariables($this->getPlayerName());
        $amount = $origin->replaceVariables($this->getAmount());

        $this->throwIfInvalidNumber($amount, 1);

        $economy = Economy::getPlugin();
        $economy->takeMoney($name, (int)$amount);
        return true;
    }
}