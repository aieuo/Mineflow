<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\recipe\Recipe;

class AddItem extends TypeItem {

    protected $id = self::ADD_ITEM;

    protected $name = "action.addItem.name";
    protected $detail = "action.addItem.detail";

    public function execute(Recipe $source): \Generator {
        $this->throwIfCannotExecute();

        $item = $this->getItem($source);

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        $player->getInventory()->addItem($item);
        yield true;
    }
}