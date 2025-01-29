<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\player\permission;

use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleCondition;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\libs\_30a18b127a564f2c\SOFe\AwaitGenerator\Await;

class HasPermission extends SimpleCondition {

    public function __construct(string $player = "", string $playerPermission = "") {
        parent::__construct(self::HAS_PERMISSION, FlowItemCategory::PLAYER_PERMISSION);

        $this->setArguments([
            PlayerArgument::create("player", $player),
            StringArgument::create("permission", $playerPermission)->example("mineflow.customcommand.op"),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->getArgument("player");
    }

    public function getPlayerPermission(): StringArgument {
        return $this->getArgument("permission");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $player = $this->getPlayer()->getOnlinePlayer($source);
        $permission = $this->getPlayerPermission()->getString($source);

        yield Await::ALL;
        return $player->hasPermission($permission);
    }
}