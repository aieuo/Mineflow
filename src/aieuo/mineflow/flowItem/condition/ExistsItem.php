<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\recipe\Recipe;

class ExistsItem extends TypeItem {

    protected $id = self::EXISTS_ITEM;

    protected $name = "condition.existsItem.name";
    protected $detail = "condition.existsItem.detail";

    public function execute(Recipe $origin): \Generator {
        $this->throwIfCannotExecute();

        $item = $this->getItem($origin);
        $this->throwIfInvalidItem($item);

        $player = $this->getPlayer($origin);
        $this->throwIfInvalidPlayer($player);

        yield true;
        return $player->getInventory()->contains($item);
    }
}