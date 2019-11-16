<?php

namespace aieuo\mineflow\action\process;

use pocketmine\entity\Entity;
use pocketmine\Player;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;

class SendTip extends TypeMessage {

    protected $id = self::SEND_TIP;

    protected $name = "@action.sendTip.name";
    protected $description = "@action.sendTip.description";
    protected $detail = "action.sendTip.detail";

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;

    public function execute(?Entity $target, ?Recipe $original = null): ?bool {
        if (!($target instanceof Player)) return false;

        if (!$this->isDataValid()) {
            $target->sendMessage(Language::get("invalid.contents", [$this->getName()]));
            return false;
        }

        $target->sendTip($this->getMessage());
        return true;
    }
}