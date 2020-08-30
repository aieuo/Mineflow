<?php

namespace aieuo\mineflow\flowItem\action;

use pocketmine\Server;
use aieuo\mineflow\recipe\Recipe;

class SendMessageToOp extends TypeMessage {

    protected $id = self::SEND_MESSAGE_TO_OP;

    protected $name = "action.sendMessageToOp.name";
    protected $detail = "action.sendMessageToOp.detail";

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $message = $origin->replaceVariables($this->getMessage());
        $players = Server::getInstance()->getOnlinePlayers();
        foreach ($players as $player) {
            if ($player->isOp()) {
                $player->sendMessage($message);
            }
        }
        yield true;
        return true;
    }
}