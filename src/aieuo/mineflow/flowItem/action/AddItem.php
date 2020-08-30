<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\recipe\Recipe;

class AddItem extends TypeItem {

    protected $id = self::ADD_ITEM;

    protected $name = "action.addItem.name";
    protected $detail = "action.addItem.detail";

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;

    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $item = $this->getItem($origin);
        $this->throwIfInvalidItem($item);

        $player = $this->getPlayer($origin);
        $this->throwIfInvalidPlayer($player);

        $player->getInventory()->addItem($item);
        yield true;
        return true;
    }
}