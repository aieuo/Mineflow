<?php

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class SendPopup extends TypePlayerMessage {

    protected string $id = self::SEND_POPUP;

    protected string $name = "action.sendPopup.name";
    protected string $detail = "action.sendPopup.detail";

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $message = $source->replaceVariables($this->getMessage());

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        $player->sendPopup($message);
        yield true;
    }
}