<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use SOFe\AwaitGenerator\Await;

class Chat extends TypePlayerMessage {

    public function __construct(string $player = "", string $message = "") {
        parent::__construct(self::CHAT, player: $player, message: $message);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $message = Language::replace($source->replaceVariables($this->getMessage()));
        $player = $this->getOnlinePlayer($source);

        $player->chat($message);

        yield Await::ALL;
    }
}
