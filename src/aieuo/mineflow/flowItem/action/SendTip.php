<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\recipe\Recipe;

class SendTip extends TypePlayerMessage {

    protected $id = self::SEND_TIP;

    protected $name = "action.sendTip.name";
    protected $detail = "action.sendTip.detail";

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $message = $origin->replaceVariables($this->getMessage());

        $player = $this->getPlayer($origin);
        $this->throwIfInvalidPlayer($player);

        $player->sendTip($message);
        return true;
    }
}