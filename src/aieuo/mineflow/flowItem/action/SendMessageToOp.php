<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\recipe\Recipe;
use pocketmine\Server;

class SendMessageToOp extends TypeMessage {

    protected $id = self::SEND_MESSAGE_TO_OP;

    protected $name = "action.sendMessageToOp.name";
    protected $detail = "action.sendMessageToOp.detail";

    public function execute(Recipe $origin): \Generator {
        $this->throwIfCannotExecute();

        $message = $origin->replaceVariables($this->getMessage());
        $players = Server::getInstance()->getOnlinePlayers();
        foreach ($players as $player) {
            if ($player->isOp()) {
                $player->sendMessage($message);
            }
        }
        yield true;
    }
}