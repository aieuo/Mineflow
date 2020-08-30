<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\recipe\Recipe;

class RemoveItemAll extends TypeItem {

    protected $id = self::REMOVE_ITEM_ALL;

    protected $name = "action.removeItemAll.name";
    protected $detail = "action.removeItemAll.detail";

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;

    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $item = $this->getItem($origin);
        $this->throwIfInvalidItem($item);

        $player = $this->getPlayer($origin);
        $this->throwIfInvalidPlayer($player);

        $player->getInventory()->remove($item);
        yield true;
        return true;
    }
}