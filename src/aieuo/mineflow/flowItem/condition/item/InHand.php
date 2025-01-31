<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\item;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\libs\_6b4cfdc0a11de6c9\SOFe\AwaitGenerator\Await;

class InHand extends TypeItem {

    public function __construct(string $player = "", string $item = "") {
        parent::__construct(self::IN_HAND, player: $player, item: $item);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $item = $this->getItem()->getItem($source);
        $player = $this->getPlayer()->getOnlinePlayer($source);

        $hand = $player->getInventory()->getItemInHand();

        yield Await::ALL;
        return $hand->equals($item, true, true) and $hand->getCount() >= $item->getCount();
    }
}