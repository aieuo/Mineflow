<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class InHand extends TypeItem {

    protected string $name = "condition.inHand.name";
    protected string $detail = "condition.inHand.detail";

    public function __construct(string $player = "", string $item = "") {
        parent::__construct(self::IN_HAND, player: $player, item: $item);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $item = $this->getItem($source);

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        $hand = $player->getInventory()->getItemInHand();

        yield true;
        return ($hand->getId() === $item->getId()
            and $hand->getMeta() === $item->getMeta()
            and $hand->getCount() >= $item->getCount()
            and (!$item->hasCustomName() or $hand->getName() === $item->getName())
            and (empty($item->getLore()) or $item->getLore() === $hand->getLore())
            and (empty($item->getEnchantments()) or $item->getEnchantments() === $hand->getEnchantments())
        );
    }
}