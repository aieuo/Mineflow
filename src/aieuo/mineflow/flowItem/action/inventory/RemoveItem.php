<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\inventory;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class RemoveItem extends TypeItem {

    protected string $name = "action.removeItem.name";
    protected string $detail = "action.removeItem.detail";

    public function __construct(string $player = "", string $item = "") {
        parent::__construct(self::REMOVE_ITEM, player: $player, item: $item);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $item = $this->getItem($source);

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        $player->getInventory()->removeItem($item);
        yield true;
    }
}