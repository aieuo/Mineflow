<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\inventory;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\libs\_30a18b127a564f2c\SOFe\AwaitGenerator\Await;

class RemoveItemAll extends TypeItem {

    public function __construct(string $player = "", string $item = "") {
        parent::__construct(self::REMOVE_ITEM_ALL, player: $player, item: $item);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $item = $this->getItem()->getItem($source);
        $player = $this->getPlayer()->getOnlinePlayer($source);

        $player->getInventory()->remove($item);

        yield Await::ALL;
    }
}