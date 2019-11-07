<?php

namespace aieuo\mineflow\action\process;

use pocketmine\entity\Entity;
use pocketmine\Player;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;

class SendPopup extends TypeMessage {

    protected $id = self::SEND_POPUP;

    protected $name = "@process.sendpopup.name";
    protected $description = "@process.sendpopup.description";
    protected $detail = "process.sendpopup.detail";

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;

    public function execute(?Entity $target, ?Recipe $original = null): ?bool {
        if (!($target instanceof Player)) return false;

        if (!$this->isDataValid()) {
            $target->sendMessage(Language::get("input.invalid", [$this->getName()]));
            return false;
        }

        $target->sendPopup($this->getMessage());
        return true;
    }
}