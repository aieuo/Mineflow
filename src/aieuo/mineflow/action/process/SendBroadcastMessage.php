<?php

namespace aieuo\mineflow\action\process;

use pocketmine\entity\Entity;
use pocketmine\Player;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;
use pocketmine\Server;

class SendBroadcastMessage extends TypeMessage {

    protected $id = self::SEND_BROADCAST_MESSAGE;

    protected $name = "@action.broadcastMessage.name";
    protected $description = "@action.broadcastMessage.description";
    protected $detail = "action.broadcastMessage.detail";

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    public function execute(?Entity $target, ?Recipe $original = null): ?bool {
        if (!$this->isDataValid()) {
            if ($target instanceof Player) $target->sendMessage(Language::get("invalid.contents", [$this->getName()]));
            else Server::getInstance()->getLogger()->info(Language::get("invalid.contents", [$this->getName()]));
            return false;
        }

        Server::getInstance()->broadcastMessage($this->getMessage());
        return true;
    }
}