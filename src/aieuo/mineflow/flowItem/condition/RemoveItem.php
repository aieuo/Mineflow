<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\recipe\Recipe;

class RemoveItem extends TypeItem {

    protected $id = self::REMOVE_ITEM;

    protected $name = "condition.removeItem.name";
    protected $detail = "condition.removeItem.detail";

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;
    protected $returnValueType = self::RETURN_NONE;

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $item = $this->getItem($origin);
        $this->throwIfInvalidItem($item);

        $player = $this->getPlayer($origin);
        $this->throwIfInvalidPlayer($player);

        if (!$player->getInventory()->contains($item)) return false;
        $player->getInventory()->removeItem($item);
        return true;
    }
}