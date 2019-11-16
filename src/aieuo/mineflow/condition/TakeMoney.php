<?php

namespace aieuo\mineflow\condition;

use pocketmine\utils\TextFormat;
use pocketmine\entity\Entity;
use pocketmine\Player;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\economy\Economy;

class TakeMoney extends TypeMoney {

    protected $id = self::TAKE_MONEY;

    protected $name = "@condition.takeMoney.name";
    protected $description = "@condition.takeMoney.description";
    protected $detail = "condition.takeMoney.detail";

    public function execute(?Entity $target, ?Recipe $origin = null): ?bool {
        if (!($target instanceof Player)) return null;

        if (!$this->isDataValid()) {
            $target->sendMessage(Language::get("invalid.contents", [$this->getName()]));
            return null;
        }
        if (!Economy::isPluginLoaded()) {
            $target->sendMessage(TextFormat::RED.Language::get("economy.notfound"));
            return null;
        }

        $economy = Economy::getPlugin();
        $mymoney = $economy->getMoney($target->getName());
        if ($mymoney >= $this->getAmount()) {
            $economy->takeMoney($target->getName(), $this->getAmount());
            return true;
        }

        return false;
    }
}