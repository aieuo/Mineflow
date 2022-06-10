<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use pocketmine\Server;

class SendMessageToOp extends TypeMessage {

    public function __construct(string $message = "") {
        parent::__construct(self::SEND_MESSAGE_TO_OP, message: $message);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $message = Language::replace($source->replaceVariables($this->getMessage()));
        $players = Server::getInstance()->getOnlinePlayers();
        foreach ($players as $player) {
            if (Server::getInstance()->isOp($player->getName())) {
                $player->sendMessage($message);
            }
        }
        yield true;
    }
}