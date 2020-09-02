<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\recipe\Recipe;
use pocketmine\Server;

class BroadcastMessage extends TypeMessage {

    protected $id = self::BROADCAST_MESSAGE;

    protected $name = "action.broadcastMessage.name";
    protected $detail = "action.broadcastMessage.detail";

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $message = $origin->replaceVariables($this->getMessage());
        Server::getInstance()->broadcastMessage($message);
        yield true;
        return true;
    }
}