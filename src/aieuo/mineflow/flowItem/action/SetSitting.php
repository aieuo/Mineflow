<?php

namespace aieuo\mineflow\flowItem\action;

use pocketmine\network\mcpe\protocol\types\EntityLink;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\level\Position;
use pocketmine\entity\Entity;
use pocketmine\Server;
use pocketmine\Player;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;

class SetSitting extends TypePosition {

    protected $id = self::SET_SITTING;

    protected $name = "action.setSitting.name";
    protected $detail = "action.setSitting.detail";

    protected $category = Categories::CATEGORY_ACTION_PLAYER;

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;

    /** @var array */
    private static $entityIds = [];

    public function execute(?Entity $target, Recipe $origin): ?bool {
        if (!$this->canExecute($target)) return null;

        $positions = array_map(function ($value) use ($origin) {
            return $origin->replaceVariables($value);
        }, $this->getPosition());

        $level = Server::getInstance()->getLevelByName($positions[3]);
        if (!is_numeric($positions[0]) or !is_numeric($positions[1]) or !is_numeric($positions[2])) {
            $target->sendMessage(Language::get("flowItem.error", [$this->getName(), Language::get("flowItem.error.notNumber")]));
            return null;
        }
        if ($level === null) {
            $target->sendMessage(Language::get("flowItem.error", [$this->getName(), Language::get("action.position.level.notFound")]));
            return null;
        }

        $position = new Position((float)$positions[0], (float)$positions[1], (float)$positions[2], $level);

        $pk = new AddActorPacket();
        $pk->entityRuntimeId = ++Entity::$entityCount;
        $pk->type = 84;
        $pk->position = $position;
        $pk->links = [new EntityLink($pk->entityRuntimeId, $target->getId(), EntityLink::TYPE_RIDER)];
        $pk->metadata = [
            Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, 1 << Entity::DATA_FLAG_INVISIBLE]
        ];
        $target->dataPacket($pk);
        /** @var Player $target */
        self::leave($target);
        self::$entityIds[$target->getName()] = $pk->entityRuntimeId;
        return true;
    }

    public static function leave(Player $player) {
        if (isset(self::$entityIds[$player->getName()])) {
            $pk = new RemoveActorPacket();
            $pk->entityUniqueId = self::$entityIds[$player->getName()];
            if ($player->isOnline()) $player->dataPacket($pk);
            unset(self::$entityIds[$player->getName()]);
        }
    }
}