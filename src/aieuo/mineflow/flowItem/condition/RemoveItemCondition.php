<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class RemoveItemCondition extends TypeItem {

    protected string $name = "condition.removeItemCondition.name";
    protected string $detail = "condition.removeItemCondition.detail";

    public function __construct(string $player = "", string $item = "") {
        parent::__construct(self::REMOVE_ITEM_CONDITION, player: $player, item: $item);
    }

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