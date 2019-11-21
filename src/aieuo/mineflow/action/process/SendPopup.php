<?php

namespace aieuo\mineflow\action\process;

use pocketmine\entity\Entity;
use pocketmine\Player;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;

class SendPopup extends TypeMessage {

    protected $id = self::SEND_POPUP;

    protected $name = "@action.sendPopup.name";
    protected $description = "@action.sendPopup.description";
    protected $detail = "action.sendPopup.detail";

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;

    public function execute(?Entity $target, ?Recipe $origin = null): ?bool {
        if (!($target instanceof Player)) return false;

        if (!$this->isDataValid()) {
            $target->sendMessage(Language::get("invalid.contents", [$this->getName()]));
            return false;
        }

        $message = $this->getMessage();
        if ($origin instanceof Recipe) {
            $message = $origin->replaceVariables($message);
        }

        $target->sendPopup($message);
        return true;
    }
}