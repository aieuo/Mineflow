<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\inventory;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class RemoveItemAll extends TypeItem {

    protected string $id = self::REMOVE_ITEM_ALL;

    protected string $name = "action.removeItemAll.name";
    protected string $detail = "action.removeItemAll.detail";

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $item = $this->getItem($source);

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        $player->getInventory()->remove($item);
        yield FlowItemExecutor::CONTINUE;
    }
}