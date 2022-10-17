<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\inventory;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class AddItem extends TypeItem {

    public function __construct(string $player = "", string $item = "") {
        parent::__construct(self::ADD_ITEM, player: $player, item: $item);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $item = $this->getItem($source);
        $player = $this->getOnlinePlayer($source);

        $player->getInventory()->addItem($item);

        yield Await::ALL;
    }
}
