<?php

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class SendMessage extends TypePlayerMessage {

    protected $id = self::SEND_MESSAGE;

    protected $name = "action.sendMessage.name";
    protected $detail = "action.sendMessage.detail";

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $message = $source->replaceVariables($this->getMessage());

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        $player->sendMessage($message);
        yield true;
    }
}