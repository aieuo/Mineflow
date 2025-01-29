<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player\message;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\libs\_3ced88d4028c9717\SOFe\AwaitGenerator\Await;

class SendPopup extends TypePlayerMessage {

    public function __construct(string $player = "", string $message = "") {
        parent::__construct(self::SEND_POPUP, player: $player, message: $message);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $message = Language::replace($this->getMessage()->getString($source));
        $player = $this->getPlayer()->getOnlinePlayer($source);

        $player->sendPopup($message);

        yield Await::ALL;
    }
}