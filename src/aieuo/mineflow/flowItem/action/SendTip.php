<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\recipe\Recipe;

class SendTip extends TypePlayerMessage {

    protected $id = self::SEND_TIP;

    protected $name = "action.sendTip.name";
    protected $detail = "action.sendTip.detail";

    public function execute(Recipe $source): \Generator {
        $this->throwIfCannotExecute();

        $message = $source->replaceVariables($this->getMessage());

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        $player->sendTip($message);
        yield true;
    }
}