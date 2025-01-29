<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\inventory;

use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\libs\_3ced88d4028c9717\SOFe\AwaitGenerator\Await;

class ClearInventory extends SimpleAction {

    public function __construct(string $player = "") {
        parent::__construct(self::CLEAR_INVENTORY, FlowItemCategory::INVENTORY);

        $this->setArguments([
            PlayerArgument::create("player", $player),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->getArgument("player");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $player = $this->getPlayer()->getOnlinePlayer($source);

        $player->getInventory()->clearAll();

        yield Await::ALL;
    }
}