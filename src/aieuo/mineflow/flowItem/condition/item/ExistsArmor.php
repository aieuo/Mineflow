<?php
declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\item;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class ExistsArmor extends TypeItem {

    public function __construct(string $player = "", string $item = "") {
        parent::__construct(self::EXISTS_ARMOR, player: $player, item: $item);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $item = $this->getItem($source);
        $player = $this->getOnlinePlayer($source);

        yield Await::ALL;
        return $player->getArmorInventory()->contains($item);
    }
}
