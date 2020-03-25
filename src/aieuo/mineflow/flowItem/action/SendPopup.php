<?php

namespace aieuo\mineflow\flowItem\action;

use pocketmine\entity\Entity;
use aieuo\mineflow\recipe\Recipe;

class SendPopup extends TypeMessage {

    protected $id = self::SEND_POPUP;

    protected $name = "action.sendPopup.name";
    protected $detail = "action.sendPopup.detail";

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;

    public function execute(?Entity $target, Recipe $origin): ?bool {
        if (!$this->canExecute($target)) return null;

        $message = $origin->replaceVariables($this->getMessage());
        $target->sendPopup($message);
        return true;
    }
}