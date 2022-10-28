<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use SOFe\AwaitGenerator\Await;

class SendMessage extends TypePlayerMessage {

    public function __construct(string $player = "", string $message = "") {
        parent::__construct(self::SEND_MESSAGE, player: $player, message: $message);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $message = Language::replace($source->replaceVariables($this->getMessage()));
        $player = $this->getOnlinePlayer($source);

        $player->sendMessage($message);

        yield Await::ALL;
    }
}
