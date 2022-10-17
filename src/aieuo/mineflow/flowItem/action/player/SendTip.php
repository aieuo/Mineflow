<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use SOFe\AwaitGenerator\Await;

class SendTip extends TypePlayerMessage {

    public function __construct(string $player = "", string $message = "") {
        parent::__construct(self::SEND_TIP, player: $player, message: $message);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $message = Language::replace($source->replaceVariables($this->getMessage()));
        $player = $this->getOnlinePlayer($source);

        $player->sendTip($message);

        yield Await::ALL;
    }
}
