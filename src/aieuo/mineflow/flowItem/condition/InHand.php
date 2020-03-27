<?php

namespace aieuo\mineflow\flowItem\condition;

use pocketmine\entity\Entity;
use pocketmine\Player;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;

class InHand extends TypeItem {

    protected $id = self::IN_HAND;

    protected $name = "condition.inHand.name";
    protected $detail = "condition.inHand.detail";

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;
    protected $returnValueType = self::RETURN_NONE;

    public function execute(?Entity $target, Recipe $origin): bool {
        $this->throwIfCannotExecute($target);

        $item = $this->getItem();
        $id = $origin->replaceVariables($item[0]);
        $count = $origin->replaceVariables($item[1]);
        $name = $origin->replaceVariables($item[2]);

        if (!$this->checkValidNumberDataAndAlert($count, 1, null, $target)) return null;

        $item = $this->parseItem($id, (int)$count, $name);
        if ($item === null) {
            $target->sendMessage(Language::get("flowItem.error", [$this->getName(), Language::get("condition.item.notFound")]));
            return null;
        }

        /** @var Player $target */
        $hand = $target->getInventory()->getItemInHand();
        return ($hand->getId() === $item->getId()
            and $hand->getDamage() === $item->getDamage()
            and $hand->getCount() >= $item->getCount()
            and (
                !$item->hasCustomName()
                or $hand->getName() === $item->getName()
            )
        );
    }
}