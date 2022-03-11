<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class ExistsItem extends TypeItem {

    protected string $name = "condition.existsItem.name";
    protected string $detail = "condition.existsItem.detail";

    public function __construct(string $player = "", string $item = "") {
        parent::__construct(self::EXISTS_ITEM, player: $player, item: $item);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $item = $this->getItem($source);

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        yield true;
        return $player->getInventory()->contains($item);
    }
}