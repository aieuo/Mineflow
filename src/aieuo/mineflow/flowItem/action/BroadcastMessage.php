<?php

namespace aieuo\mineflow\flowItem\action;

use pocketmine\Server;
use aieuo\mineflow\recipe\Recipe;

class BroadcastMessage extends TypeMessage {

    protected $id = self::BROADCAST_MESSAGE;

    protected $name = "action.broadcastMessage.name";
    protected $detail = "action.broadcastMessage.detail";

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $message = $origin->replaceVariables($this->getMessage());
        Server::getInstance()->broadcastMessage($message);
        return true;
    }
}