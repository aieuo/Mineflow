<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player\message;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use pocketmine\Server;
use SOFe\AwaitGenerator\Await;

class SendMessageToOp extends TypeMessage {

    public function __construct(string $message = "") {
        parent::__construct(self::SEND_MESSAGE_TO_OP, message: $message);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $message = Language::replace($source->replaceVariables($this->getMessage()));
        $players = Server::getInstance()->getOnlinePlayers();
        foreach ($players as $player) {
            if (Server::getInstance()->isOp($player->getName())) {
                $player->sendMessage($message);
            }
        }

        yield Await::ALL;
    }
}
