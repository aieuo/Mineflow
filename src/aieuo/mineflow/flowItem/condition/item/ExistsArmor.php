<?php
declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\item;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\libs\_1195f54ac7f1c3fe\SOFe\AwaitGenerator\Await;

class ExistsArmor extends TypeItem {

    public function __construct(string $player = "", string $item = "") {
        parent::__construct(self::EXISTS_ARMOR, player: $player, item: $item);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $item = $this->getItem()->getItem($source);
        $player = $this->getPlayer()->getOnlinePlayer($source);

        yield Await::ALL;
        return $player->getArmorInventory()->contains($item);
    }
}