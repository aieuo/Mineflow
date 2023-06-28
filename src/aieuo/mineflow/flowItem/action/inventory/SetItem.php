<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\inventory;

use aieuo\mineflow\flowItem\argument\ItemArgument;
use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class SetItem extends SimpleAction {

    private PlayerArgument $player;
    private ItemArgument $item;
    private NumberArgument $index;

    public function __construct(string $player = "", string $item = "", string $index = "") {
        parent::__construct(self::SET_ITEM, FlowItemCategory::INVENTORY);

        $this->setArguments([
            $this->player = new PlayerArgument("player", $player),
            $this->item = new ItemArgument("item", $item),
            $this->index = new NumberArgument("index", $index, example: "0", min: 0),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->player;
    }

    public function getItem(): ItemArgument {
        return $this->item;
    }

    public function getIndex(): NumberArgument {
        return $this->index;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $index = $this->index->getInt($source);
        $player = $this->player->getOnlinePlayer($source);

        $item = $this->item->getItem($source);

        $player->getInventory()->setItem($index, $item);

        yield Await::ALL;
    }
}
