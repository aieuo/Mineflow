<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\item;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class InHand extends TypeItem {

    public function __construct(string $player = "", string $item = "") {
        parent::__construct(self::IN_HAND, player: $player, item: $item);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $item = $this->getItem($source);
        $player = $this->getOnlinePlayer($source);

        $hand = $player->getInventory()->getItemInHand();

        yield Await::ALL;
        return ($hand->getId() === $item->getId()
            and $hand->getMeta() === $item->getMeta()
            and $hand->getCount() >= $item->getCount()
            and (!$item->hasCustomName() or $hand->getName() === $item->getName())
            and (empty($item->getLore()) or $item->getLore() === $hand->getLore())
            and (empty($item->getEnchantments()) or $item->getEnchantments() === $hand->getEnchantments())
        );
    }
}
