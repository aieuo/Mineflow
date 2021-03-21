<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class RemoveItemCondition extends TypeItem {

    protected $id = self::REMOVE_ITEM_CONDITION;

    protected $name = "condition.removeItem.name";
    protected $detail = "condition.removeItem.detail";

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $item = $this->getItem($source);

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        if (!$player->getInventory()->contains($item)) return false;
        $player->getInventory()->removeItem($item);

        yield true;
        return true;
    }
}