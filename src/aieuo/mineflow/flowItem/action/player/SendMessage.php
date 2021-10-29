<?php

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class SendMessage extends TypePlayerMessage {

    protected string $id = self::SEND_MESSAGE;

    protected string $name = "action.sendMessage.name";
    protected string $detail = "action.sendMessage.detail";

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $message = $source->replaceVariables($this->getMessage());
        $player = $this->getOnlinePlayer($source);

        $player->sendMessage($message);
        yield FlowItemExecutor::CONTINUE;
    }
}