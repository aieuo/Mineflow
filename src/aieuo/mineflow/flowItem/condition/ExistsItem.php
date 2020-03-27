<?php

namespace aieuo\mineflow\flowItem\condition;

use pocketmine\entity\Entity;
use pocketmine\Player;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;

class ExistsItem extends TypeItem {

    protected $id = self::EXISTS_ITEM;

    protected $name = "condition.existsItem.name";
    protected $detail = "condition.existsItem.detail";

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;
    protected $returnValueType = self::RETURN_NONE;

    public function execute(?Entity $target, Recipe $origin): bool {
        $this->throwIfCannotExecute($target);

        $item = $this->getItem($origin);
        $this->throwIfInvalidItem($item);

        $player = $this->getPlayer($origin);
        $this->throwIfInvalidPlayer($player);

        return $player->getInventory()->contains($item);
    }
}