<?php

namespace aieuo\mineflow\action\process;

use pocketmine\entity\Entity;
use pocketmine\Player;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;
use pocketmine\Server;

class SendBroadcastMessage extends TypeMessage {

    protected $id = self::SEND_BROADCAST_MESSAGE;

    protected $name = "@process.broadcastmessage.name";
    protected $description = "@process.broadcastmessage.description";
    protected $detail = "process.broadcastmessage.detail";

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    public function execute(?Entity $target, ?Recipe $original = null): ?bool {
        if (!$this->isDataValid()) {
            if ($target instanceof Player) $target->sendMessage(Language::get("input.invalid", [$this->getName()]));
            else Server::getInstance()->getLogger()->info(Language::get("input.invalid", [$this->getName()]));
            return false;
        }

        Server::getInstance()->broadcastMessage($this->getMessage());
        return true;
    }
}