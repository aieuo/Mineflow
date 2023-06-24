<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\inventory;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class SetItemInHand extends TypeItem {

    public function __construct(string $player = "", string $item = "") {
        parent::__construct(self::SET_ITEM_IN_HAND, player: $player, item: $item);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $item = $this->item->getItem($source);
        $player = $this->getOnlinePlayer($source);

        $player->getInventory()->setItemInHand($item);

        yield Await::ALL;
    }
}
