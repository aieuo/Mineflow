<?php

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class SendTip extends TypePlayerMessage {

    protected string $id = self::SEND_TIP;

    protected string $name = "action.sendTip.name";
    protected string $detail = "action.sendTip.detail";

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $message = $source->replaceVariables($this->getMessage());
        $player = $this->getOnlinePlayer($source);

        $player->sendTip($message);
        yield FlowItemExecutor::CONTINUE;
    }
}