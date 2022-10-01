<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\inventory;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class SetItemInHand extends TypeItem {

    protected string $name = "action.setItemInHand.name";
    protected string $detail = "action.setItemInHand.detail";

    public function __construct(string $player = "", string $item = "") {
        parent::__construct(self::SET_ITEM_IN_HAND, player: $player, item: $item);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $item = $this->getItem($source);

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        $player->getInventory()->setItemInHand($item);
        yield true;
    }
}