<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\player\permission;

use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleCondition;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class HasPermission extends SimpleCondition {

    public function __construct(string $player = "", string $playerPermission = "") {
        parent::__construct(self::HAS_PERMISSION, FlowItemCategory::PLAYER_PERMISSION);

        $this->setArguments([
            new PlayerArgument("player", $player),
            new StringArgument("permission", $playerPermission, example: "mineflow.customcommand.op"),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->getArguments()[0];
    }

    public function getPlayerPermission(): StringArgument {
        return $this->getArguments()[1];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $player = $this->getPlayer()->getOnlinePlayer($source);
        $permission = $this->getPlayerPermission()->get();

        yield Await::ALL;
        return $player->hasPermission($permission);
    }
}
