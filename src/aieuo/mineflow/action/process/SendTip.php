<?php

namespace aieuo\mineflow\action\process;

use pocketmine\entity\Entity;
use pocketmine\Player;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;

class SendTip extends TypeMessage {

    protected $id = self::SEND_TIP;

    protected $name = "@process.sendtip.name";
    protected $description = "@process.sendtip.description";
    protected $detail = "process.sendtip.detail";

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;

    public function execute(?Entity $target, ?Recipe $original = null): ?bool {
        if (!($target instanceof Player)) return false;

        if (!$this->isDataValid()) {
            $target->sendMessage(Language::get("input.invalid", [$this->getName()]));
            return false;
        }

        $target->sendTip($this->getMessage());
        return true;
    }
}