<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\recipe\Recipe;

class SendMessage extends TypePlayerMessage {

    protected $id = self::SEND_MESSAGE;

    protected $name = "action.sendMessage.name";
    protected $detail = "action.sendMessage.detail";

    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $message = $origin->replaceVariables($this->getMessage());

        $player = $this->getPlayer($origin);
        $this->throwIfInvalidPlayer($player);

        $player->sendMessage($message);
        yield true;
    }
}