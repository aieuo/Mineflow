<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\inventory;

use aieuo\mineflow\flowItem\argument\ItemArgument;
use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;

abstract class TypeItem extends SimpleAction {

    protected PlayerArgument $player;
    protected ItemArgument $item;

    public function __construct(
        string $id,
        string $category = FlowItemCategory::INVENTORY,
        string $player = "",
        string $item = ""
    ) {
        parent::__construct($id, $category);

        $this->setArguments([
            $this->player = new PlayerArgument("player", $player),
            $this->item = new ItemArgument("item", $item),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->player;
    }

    public function getItem(): ItemArgument {
        return $this->item;
    }
}
