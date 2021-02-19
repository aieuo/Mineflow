<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\recipe\Recipe;

class AddItem extends TypeItem {

    protected $id = self::ADD_ITEM;

    protected $name = "action.addItem.name";
    protected $detail = "action.addItem.detail";

    public function execute(Recipe $origin): \Generator {
        $this->throwIfCannotExecute();

        $item = $this->getItem($origin);

        $player = $this->getPlayer($origin);
        $this->throwIfInvalidPlayer($player);

        $player->getInventory()->addItem($item);
        yield true;
    }
}