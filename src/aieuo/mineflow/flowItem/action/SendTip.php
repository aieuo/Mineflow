<?php

namespace aieuo\mineflow\flowItem\action;

use pocketmine\entity\Entity;
use aieuo\mineflow\recipe\Recipe;

class SendTip extends TypeMessage {

    protected $id = self::SEND_TIP;

    protected $name = "action.sendTip.name";
    protected $detail = "action.sendTip.detail";

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;

    public function execute(?Entity $target, Recipe $origin): bool {
        $this->throwIfCannotExecute($target);

        $message = $origin->replaceVariables($this->getMessage());
        $target->sendTip($message);
        return true;
    }
}