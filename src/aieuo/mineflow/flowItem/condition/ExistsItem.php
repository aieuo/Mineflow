<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class ExistsItem extends TypeItem {

    protected string $id = self::EXISTS_ITEM;

    protected string $name = "condition.existsItem.name";
    protected string $detail = "condition.existsItem.detail";

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $item = $this->getItem($source);

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        FlowItemExexutor::CONTINUE;
        return $player->getInventory()->contains($item);
    }
}