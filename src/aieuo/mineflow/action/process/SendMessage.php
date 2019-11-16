<?php

namespace aieuo\mineflow\action\process;

use pocketmine\entity\Entity;
use pocketmine\Player;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;

class SendMessage extends TypeMessage {

    protected $id = self::SEND_MESSAGE;

    protected $name = "@action.sendMessage.name";
    protected $description = "@action.sendMessage.description";
    protected $detail = "action.sendMessage.detail";

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;

    public function execute(?Entity $target, ?Recipe $original = null): ?bool {
        if (!($target instanceof Player)) return false;

        if (!$this->isDataValid()) {
            $target->sendMessage(Language::get("invalid.contents", [$this->getName()]));
            return false;
        }

        $target->sendMessage($this->getMessage());
        return true;
    }
}