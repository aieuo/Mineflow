<?php

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;

class Chat extends TypePlayerMessage {

    protected string $name = "action.chat.name";
    protected string $detail = "action.chat.detail";

    public function __construct(string $player = "", string $message = "") {
        parent::__construct(self::CHAT, player: $player, message: $message);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $message = Language::replace($source->replaceVariables($this->getMessage()));

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        $player->chat($message);
        yield true;
    }
}