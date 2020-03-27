<?php

namespace aieuo\mineflow\flowItem\action;

use pocketmine\entity\Entity;
use aieuo\mineflow\recipe\Recipe;

class SendTip extends TypePlayerMessage {

    protected $id = self::SEND_TIP;

    protected $name = "action.sendTip.name";
    protected $detail = "action.sendTip.detail";

    public function execute(?Entity $target, Recipe $origin): bool {
        $this->throwIfCannotExecute($target);

        $message = $origin->replaceVariables($this->getMessage());

        $player = $this->getPlayer($origin);
        $this->throwIfInvalidPlayer($player);

        $player->sendTip($message);
        return true;
    }
}