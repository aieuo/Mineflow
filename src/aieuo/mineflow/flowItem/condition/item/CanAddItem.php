<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\item;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class CanAddItem extends TypeItem {

    public function __construct(string $player = "", string $item = "") {
        parent::__construct(self::CAN_ADD_ITEM, player: $player, item: $item);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $item = $this->item->getItem($source);
        $player = $this->player->getOnlinePlayer($source);

        yield Await::ALL;
        return $player->getInventory()->canAddItem($item);
    }
}
