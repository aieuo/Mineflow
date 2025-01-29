<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player\message;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\libs\_ddbd776e705b8daa\SOFe\AwaitGenerator\Await;

class Chat extends TypePlayerMessage {

    public function __construct(string $player = "", string $message = "") {
        parent::__construct(self::CHAT, player: $player, message: $message);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $message = Language::replace($this->getMessage()->getString($source));
        $player = $this->getPlayer()->getOnlinePlayer($source);

        $player->chat($message);

        yield Await::ALL;
    }
}