<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\item;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class ExistsItem extends TypeItem {

    public function __construct(string $player = "", string $item = "") {
        parent::__construct(self::EXISTS_ITEM, player: $player, item: $item);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $item = $this->getItem($source);

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        yield Await::ALL;
        return $player->getInventory()->contains($item);
    }
}
