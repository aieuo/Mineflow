<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\inventory;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\libs\_ddbd776e705b8daa\SOFe\AwaitGenerator\Await;

class AddItem extends TypeItem {

    public function __construct(string $player = "", string $item = "") {
        parent::__construct(self::ADD_ITEM, player: $player, item: $item);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $item = $this->getItem()->getItem($source);
        $player = $this->getPlayer()->getOnlinePlayer($source);

        $player->getInventory()->addItem($item);

        yield Await::ALL;
    }
}