<?php

namespace aieuo\mineflow\flowItem\action;

use pocketmine\level\Position;
use pocketmine\entity\Entity;
use pocketmine\Server;
use aieuo\mineflow\utils\Logger;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;

class Teleport extends TypePosition {

    protected $id = self::TELEPORT;

    protected $name = "action.teleport.name";
    protected $detail = "action.teleport.detail";

    protected $category = Categories::CATEGORY_ACTION_ENTITY;

    protected $targetRequired = Recipe::TARGET_REQUIRED_ENTITY;

    public function execute(?Entity $target, Recipe $origin): ?bool {
        if (!$this->canExecute($target)) return null;

        $positions = array_map(function ($value) use ($origin) {
            return $origin->replaceVariables($value);
        }, $this->getPosition());

        if (!is_numeric($positions[0]) or !is_numeric($positions[1]) or !is_numeric($positions[2])) {
            Logger::warning(Language::get("flowItem.error", [$this->getName(), Language::get("flowItem.error.notNumber")]), $target);
            return null;
        }

        $level = Server::getInstance()->getLevelByName($positions[3]);
        if ($level === null) {
            Logger::warning(Language::get("flowItem.error", [$this->getName(), Language::get("action.position.level.notFound")]), $target);
            return null;
        }

        $position = new Position((float)$positions[0], (float)$positions[1], (float)$positions[2], $level);
        $target->teleport($position);
        return true;
    }
}