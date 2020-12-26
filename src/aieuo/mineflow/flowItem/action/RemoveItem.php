<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\recipe\Recipe;

class RemoveItem extends TypeItem {

    protected $id = self::REMOVE_ITEM;

    protected $name = "action.removeItem.name";
    protected $detail = "action.removeItem.detail";

    public function execute(Recipe $origin): \Generator {
        $this->throwIfCannotExecute();

        $item = $this->getItem($origin);
        $this->throwIfInvalidItem($item);

        $player = $this->getPlayer($origin);
        $this->throwIfInvalidPlayer($player);

        $player->getInventory()->removeItem($item);
        yield true;
    }
}