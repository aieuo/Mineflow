<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\recipe\Recipe;

class RemoveItemCondition extends TypeItem {

    protected $id = self::REMOVE_ITEM_CONDITION;

    protected $name = "condition.removeItem.name";
    protected $detail = "condition.removeItem.detail";

    public function execute(Recipe $origin): \Generator {
        $this->throwIfCannotExecute();

        $item = $this->getItem($origin);

        $player = $this->getPlayer($origin);
        $this->throwIfInvalidPlayer($player);

        if (!$player->getInventory()->contains($item)) return false;
        $player->getInventory()->removeItem($item);

        yield true;
        return true;
    }
}