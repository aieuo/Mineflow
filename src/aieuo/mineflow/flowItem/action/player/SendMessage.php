<?php

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;

class SendMessage extends TypePlayerMessage {

    protected string $name = "action.sendMessage.name";
    protected string $detail = "action.sendMessage.detail";

    public function __construct(string $player = "", string $message = "") {
        parent::__construct(self::SEND_MESSAGE, player: $player, message: $message);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $message = Language::replace($source->replaceVariables($this->getMessage()));

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        $player->sendMessage($message);
        yield true;
    }
}