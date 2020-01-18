<?php

namespace aieuo\mineflow\flowItem\action;

use pocketmine\entity\Entity;
use pocketmine\Player;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;

class SendTip extends TypeMessage {

    protected $id = self::SEND_TIP;

    protected $name = "action.sendTip.name";
    protected $detail = "action.sendTip.detail";

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;

    public function execute(?Entity $target, Recipe $origin): ?bool {
        if (!$this->canExecute($target)) return null;

        $message = $origin->replaceVariables($this->getMessage());
        $target->sendTip($message);
        return true;
    }
}