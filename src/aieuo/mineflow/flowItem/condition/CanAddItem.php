<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\recipe\Recipe;

class CanAddItem extends TypeItem {

    protected $id = self::CAN_ADD_ITEM;

    protected $name = "condition.canAddItem.name";
    protected $detail = "condition.canAddItem.detail";

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;
    protected $returnValueType = self::RETURN_NONE;

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $item = $this->getItem($origin);
        $this->throwIfInvalidItem($item);

        $player = $this->getPlayer($origin);
        $this->throwIfInvalidPlayer($player);

        return $player->getInventory()->canAddItem($item);
    }
}