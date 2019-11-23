<?php

namespace aieuo\mineflow\action\process;

use pocketmine\entity\Entity;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Categories;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\Server;

class SetSleeping extends TypePosition {

    protected $id = self::SET_SLEEPING;

    protected $name = "@action.setSleeping.name";
    protected $description = "@action.setSleeping.description";
    protected $detail = "action.setSleeping.detail";

    protected $category = Categories::CATEGORY_ACTION_PLAYER;

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;

    public function execute(?Entity $target, ?Recipe $origin = null): ?bool {
        if (!($target instanceof Player)) return false;

        if (!$this->isDataValid()) {
            $target->sendMessage(Language::get("invalid.contents", [$this->getName()]));
            return null;
        }

        $positions = $this->getPosition();
        if ($origin instanceof Recipe) {
            $positions = array_map(function ($value) use ($origin) {
                return $origin->replaceVariables($value);
            }, $positions);
        }

        if (!is_numeric($positions[0]) or !is_numeric($positions[1]) or !is_numeric($positions[2])) {
            $target->sendMessage(Language::get("action.error", [$this->getName(), Language::get("mineflow.contents.notNumber")]));
            return null;
        }
        $level = Server::getInstance()->getLevelByName($positions[3]);
        if ($level === null) {
            $target->sendMessage(Language::get("action.error", [$this->getName(), Language::get("action.position.level.notFound")]));
            return null;
        }

        $position = new Position((float)$positions[0], (float)$positions[1], (float)$positions[2], $level);
        $target->sleepOn($position);
        return true;
    }
}