<?php

namespace aieuo\mineflow\flowItem\action;

use pocketmine\entity\Entity;
use pocketmine\Server;
use aieuo\mineflow\recipe\Recipe;

class BroadcastMessage extends TypeMessage {

    protected $id = self::BROADCAST_MESSAGE;

    protected $name = "action.broadcastMessage.name";
    protected $detail = "action.broadcastMessage.detail";

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    public function execute(?Entity $target, Recipe $origin): ?bool {
        if (!$this->canExecute($target)) return null;

        $message = $origin->replaceVariables($this->getMessage());
        Server::getInstance()->broadcastMessage($message);
        return true;
    }
}