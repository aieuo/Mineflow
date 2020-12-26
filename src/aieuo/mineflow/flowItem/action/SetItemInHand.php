<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\recipe\Recipe;

class SetItemInHand extends TypeItem {

    protected $id = self::SET_ITEM_IN_HAND;

    protected $name = "action.setItemInHand.name";
    protected $detail = "action.setItemInHand.detail";

    public function execute(Recipe $origin): \Generator {
        $this->throwIfCannotExecute();

        $item = $this->getItem($origin);
        $this->throwIfInvalidItem($item);

        $player = $this->getPlayer($origin);
        $this->throwIfInvalidPlayer($player);

        $player->getInventory()->setItemInHand($item);
        yield true;
    }
}