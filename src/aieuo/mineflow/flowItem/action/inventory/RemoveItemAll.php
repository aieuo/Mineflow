<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\inventory;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class RemoveItemAll extends TypeItem {

    protected $id = self::REMOVE_ITEM_ALL;

    protected $name = "action.removeItemAll.name";
    protected $detail = "action.removeItemAll.detail";

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $item = $this->getItem($source);

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        $player->getInventory()->remove($item);
        yield true;
    }
}