<?php

namespace aieuo\mineflow\condition;

use pocketmine\entity\Entity;
use pocketmine\Player;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\condition\TypeItem;

class CanAddItem extends TypeItem {

    protected $id = self::CAN_ADD_ITEM;

    protected $name = "@condition.canAddItem.name";
    protected $description = "@condition.canAddItem.description";
    protected $detail = "condition.canAddItem.detail";

    public function execute(?Entity $target, ?Recipe $origin = null): ?bool {
        if (!($target instanceof Player)) return null;

        if (!$this->isDataValid()) {
            $target->sendMessage(Language::get("invalid.contents", [$this->getName()]));
            return null;
        }

        $item = $this->getItem();
        $id = $item[0];
        $count = $item[1];
        $name = $item[2];
        if ($origin instanceof Recipe) {
            $id = $origin->replaceVariables($id);
            $count = $origin->replaceVariables($count);
            $name = $origin->replaceVariables($name);
        }

        if (!is_numeric($count)) {
            $target->sendMessage(Language::get("condition.error", [$this->getName(), Language::get("mineflow.contents.notNumber")]));
            return null;
        } elseif ((int)$count <= 0) {
            $target->sendMessage(Language::get("condition.error", [$this->getName(), Language::get("condition.item.count.zero")]));
            return null;
        }

        $item = $this->parseItem($id, (int)$count, $name);
        if ($item === null) {
            $target->sendMessage(Language::get("condition.error", [$this->getName(), Language::get("condition.item.notFound")]));
            return null;
        }

        return $target->getInventory()->canAddItem($item);
    }
}