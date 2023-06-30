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

    private PlayerArgument $player;
    private StringArgument $playerPermission;

    public function __construct(string $player = "", string $playerPermission = "") {
        parent::__construct(self::HAS_PERMISSION, FlowItemCategory::PLAYER_PERMISSION);

        $this->setArguments([
            $this->player = new PlayerArgument("player", $player),
            $this->playerPermission = new StringArgument("permission", $playerPermission, example: "mineflow.customcommand.op"),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->player;
    }

    public function getPlayerPermission(): StringArgument {
        return $this->playerPermission;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $player = $this->player->getOnlinePlayer($source);
        $permission = $this->playerPermission->get();

        yield Await::ALL;
        return $player->hasPermission($permission);
    }
}
