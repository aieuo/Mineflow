<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player\message;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use SOFe\AwaitGenerator\Await;

class SendTip extends TypePlayerMessage {

    public function __construct(string $player = "", string $message = "") {
        parent::__construct(self::SEND_TIP, player: $player, message: $message);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $message = Language::replace($this->message->getString($source));
        $player = $this->player->getOnlinePlayer($source);

        $player->sendTip($message);

        yield Await::ALL;
    }
}
