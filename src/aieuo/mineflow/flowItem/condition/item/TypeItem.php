<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\item;

use aieuo\mineflow\flowItem\argument\ItemArgument;
use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\base\SimpleCondition;
use aieuo\mineflow\flowItem\FlowItemCategory;

abstract class TypeItem extends SimpleCondition {

    public function __construct(
        string $id,
        string $category = FlowItemCategory::INVENTORY,
        string $player = "",
        string $item = "",
    ) {
        parent::__construct($id, $category);

        $this->setArguments([
            PlayerArgument::create("player", $player),
            ItemArgument::create("item", $item),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->getArguments()[0];
    }

    public function getItem(): ItemArgument {
        return $this->getArguments()[1];
    }
}
