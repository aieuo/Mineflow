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
    protected $returnValueType = self::RETURN_NONE;

    public function execute(?Entity $target, Recipe $origin): bool {
        $this->throwIfCannotExecute($target);

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