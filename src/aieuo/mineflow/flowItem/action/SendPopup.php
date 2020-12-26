<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\recipe\Recipe;

class SendPopup extends TypePlayerMessage {

    protected $id = self::SEND_POPUP;

    protected $name = "action.sendPopup.name";
    protected $detail = "action.sendPopup.detail";

    public function execute(Recipe $origin): \Generator {
        $this->throwIfCannotExecute();

        $message = $origin->replaceVariables($this->getMessage());

        $player = $this->getPlayer($origin);
        $this->throwIfInvalidPlayer($player);

        $player->sendPopup($message);
        yield true;
    }
}