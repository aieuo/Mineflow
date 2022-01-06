<?php

namespace aieuo\mineflow\flowItem\condition\item;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class CanAddItem extends TypeItem {

    protected string $name = "condition.canAddItem.name";
    protected string $detail = "condition.canAddItem.detail";

    public function __construct(string $player = "", string $item = "") {
        parent::__construct(self::CAN_ADD_ITEM, player: $player, item: $item);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $item = $this->getItem($source);

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        yield true;
        return $player->getInventory()->canAddItem($item);
    }
}