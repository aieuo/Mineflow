<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\item;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class RemoveItemCondition extends TypeItem {

    public function __construct(string $player = "", string $item = "") {
        parent::__construct(self::REMOVE_ITEM_CONDITION, player: $player, item: $item);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $item = $this->getItem($source);
        $player = $this->getOnlinePlayer($source);

        if (!$player->getInventory()->contains($item)) return false;
        $player->getInventory()->removeItem($item);

        yield Await::ALL;
        return true;
    }
}
