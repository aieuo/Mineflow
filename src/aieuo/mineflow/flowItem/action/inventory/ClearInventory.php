<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\inventory;

use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class ClearInventory extends SimpleAction {

    private PlayerArgument $player;

    public function __construct(string $player = "") {
        parent::__construct(self::CLEAR_INVENTORY, FlowItemCategory::INVENTORY);

        $this->setArguments([
            $this->player = new PlayerArgument("player", $player),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->player;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $player = $this->player->getOnlinePlayer($source);

        $player->getInventory()->clearAll();

        yield Await::ALL;
    }
}
