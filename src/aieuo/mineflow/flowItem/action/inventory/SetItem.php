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

    public function __construct(string $player = "", string $item = "", string $index = "") {
        parent::__construct(self::SET_ITEM, FlowItemCategory::INVENTORY);

        $this->setArguments([
            PlayerArgument::create("player", $player),
            ItemArgument::create("item", $item),
            NumberArgument::create("index", $index)->min(0)->example("0"),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->getArguments()[0];
    }

    public function getItem(): ItemArgument {
        return $this->getArguments()[1];
    }

    public function getIndex(): NumberArgument {
        return $this->getArguments()[2];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $index = $this->getIndex()->getInt($source);
        $player = $this->getPlayer()->getOnlinePlayer($source);

        $item = $this->getItem()->getItem($source);

        $player->getInventory()->setItem($index, $item);

        yield Await::ALL;
    }
}
