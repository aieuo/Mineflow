<?php

namespace aieuo\mineflow\flowItem\action;

use pocketmine\entity\Entity;
use aieuo\mineflow\recipe\Recipe;

class SendMessage extends TypeMessage {

    protected $id = self::SEND_MESSAGE;

    protected $name = "action.sendMessage.name";
    protected $detail = "action.sendMessage.detail";

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;

    public function execute(?Entity $target, Recipe $origin): ?bool {
        if (!$this->canExecute($target)) return null;

        $message = $origin->replaceVariables($this->getMessage());
        $target->sendMessage($message);
        return true;
    }
}