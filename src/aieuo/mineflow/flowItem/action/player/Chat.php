<?php

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class Chat extends TypePlayerMessage {

    protected string $id = self::CHAT;

    protected string $name = "action.chat.name";
    protected string $detail = "action.chat.detail";

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $message = $source->replaceVariables($this->getMessage());

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        $player->chat($message);
        yield true;
    }
}