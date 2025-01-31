<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\inventory;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\libs\_6b4cfdc0a11de6c9\SOFe\AwaitGenerator\Await;

class RemoveItem extends TypeItem {

    public function __construct(string $player = "", string $item = "") {
        parent::__construct(self::REMOVE_ITEM, player: $player, item: $item);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $item = $this->getItem()->getItem($source);
        $player = $this->getPlayer()->getOnlinePlayer($source);

        $player->getInventory()->removeItem($item);

        yield Await::ALL;
    }
}