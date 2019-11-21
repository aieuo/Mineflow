<?php

namespace aieuo\mineflow\action\process;

use pocketmine\entity\Entity;
use pocketmine\Player;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;
use pocketmine\Server;

class SendMessageToOp extends TypeMessage {

    protected $id = self::SEND_MESSAGE_TO_OP;

    protected $name = "@action.sendMessageToOp.name";
    protected $description = "@action.sendMessageToOp.description";
    protected $detail = "action.sendMessageToOp.detail";

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    public function execute(?Entity $target, ?Recipe $origin = null): ?bool {
        if (!$this->isDataValid()) {
            if ($target instanceof Player) $target->sendMessage(Language::get("invalid.contents", [$this->getName()]));
            else Server::getInstance()->getLogger()->info(Language::get("invalid.contents", [$this->getName()]));
            return false;
        }

        $message = $this->getMessage();
        if ($origin instanceof Recipe) {
            $message = $origin->replaceVariables($message);
        }

        $players = Server::getInstance()->getOnlinePlayers();
        foreach ($players as $player) {
            if ($player->isOp()) {
                $player->sendMessage($message);
            }
        }
        return true;
    }
}