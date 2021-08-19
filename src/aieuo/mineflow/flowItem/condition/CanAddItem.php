<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class CanAddItem extends TypeItem {

    protected string $id = self::CAN_ADD_ITEM;

    protected string $name = "condition.canAddItem.name";
    protected string $detail = "condition.canAddItem.detail";

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $item = $this->getItem($source);

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        yield true;
        return $player->getInventory()->canAddItem($item);
    }
}