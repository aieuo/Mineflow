<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\recipe\Recipe;

class SendMessage extends TypePlayerMessage {

    protected $id = self::SEND_MESSAGE;

    protected $name = "action.sendMessage.name";
    protected $detail = "action.sendMessage.detail";

    public function execute(Recipe $source): \Generator {
        $this->throwIfCannotExecute();

        $message = $source->replaceVariables($this->getMessage());

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        $player->sendMessage($message);
        yield true;
    }
}