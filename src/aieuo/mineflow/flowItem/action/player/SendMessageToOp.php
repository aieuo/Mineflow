<?php

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use pocketmine\Server;

class SendMessageToOp extends TypeMessage {

    protected string $id = self::SEND_MESSAGE_TO_OP;

    protected string $name = "action.sendMessageToOp.name";
    protected string $detail = "action.sendMessageToOp.detail";

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