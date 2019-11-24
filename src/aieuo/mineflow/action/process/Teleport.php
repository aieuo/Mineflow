<?php

namespace aieuo\mineflow\action\process;

use pocketmine\level\Position;
use pocketmine\entity\Entity;
use pocketmine\Server;
use aieuo\mineflow\utils\Logger;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;

class Teleport extends TypePosition {

    protected $id = self::TELEPORT;

    protected $name = "@action.teleport.name";
    protected $description = "@action.teleport.description";
    protected $detail = "action.teleport.detail";

    protected $category = Categories::CATEGORY_ACTION_ENTITY;

    protected $targetRequired = Recipe::TARGET_REQUIRED_ENTITY;

    public function execute(?Entity $target, ?Recipe $origin = null): ?bool {
        if (!($target instanceof Entity)) return false;

        if (!$this->isDataValid()) {
            Logger::warning(Language::get("invalid.contents", [$this->getName()]), $target);
            return null;
        }

        $positions = $this->getPosition();
        if ($origin instanceof Recipe) {
            $positions = array_map(function ($value) use ($origin) {
                return $origin->replaceVariables($value);
            }, $positions);
        }

        if (!is_numeric($positions[0]) or !is_numeric($positions[1]) or !is_numeric($positions[2])) {
            Logger::warning(Language::get("action.error", [$this->getName(), Language::get("mineflow.contents.notNumber")]), $target);
            return null;
        }
        $level = Server::getInstance()->getLevelByName($positions[3]);
        if ($level === null) {
            Logger::warning(Language::get("action.error", [$this->getName(), Language::get("action.position.level.notFound")]), $target);
            return null;
        }

        $position = new Position((float)$positions[0], (float)$positions[1], (float)$positions[2], $level);
        $target->teleport($position);
        return true;
    }
}