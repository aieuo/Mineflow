<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\recipe\Recipe;

class InHand extends TypeItem {

    protected $id = self::IN_HAND;

    protected $name = "condition.inHand.name";
    protected $detail = "condition.inHand.detail";

    public function execute(Recipe $source): \Generator {
        $this->throwIfCannotExecute();

        $item = $this->getItem($source);

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        $hand = $player->getInventory()->getItemInHand();

        yield true;
        return ($hand->getId() === $item->getId()
            and $hand->getDamage() === $item->getDamage()
            and $hand->getCount() >= $item->getCount()
            and (!$item->hasCustomName() or $hand->getName() === $item->getName())
            and (empty($item->getLore()) or $item->getLore() === $hand->getLore())
            and (empty($item->getEnchantments()) or $item->getEnchantments() === $hand->getEnchantments())
        );
    }
}