<?php

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;

class SendTip extends TypePlayerMessage {

    protected string $id = self::SEND_TIP;

    protected string $name = "action.sendTip.name";
    protected string $detail = "action.sendTip.detail";

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $message = Language::replace($source->replaceVariables($this->getMessage()));

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        $player->sendTip($message);
        yield true;
    }
}