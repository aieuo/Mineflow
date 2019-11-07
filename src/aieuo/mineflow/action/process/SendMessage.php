<?php

namespace aieuo\mineflow\action\process;

use pocketmine\entity\Entity;
use pocketmine\Player;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;

class SendMessage extends TypeMessage {

    protected $id = self::SEND_MESSAGE;

    protected $name = "@process.sendmessage.name";
    protected $description = "@process.sendmessage.description";
    protected $detail = "process.sendmessage.detail";

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;

    public function execute(?Entity $target, ?Recipe $original = null): ?bool {
        if (!($target instanceof Player)) return false;

        if (!$this->isDataValid()) {
            $target->sendMessage(Language::get("input.invalid", [$this->getName()]));
            return false;
        }

        $target->sendMessage($this->getMessage());
        return true;
    }
}