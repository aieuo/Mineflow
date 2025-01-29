<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player\permission;

use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\Main;
use aieuo\mineflow\libs\_3ced88d4028c9717\SOFe\AwaitGenerator\Await;

class AddPermission extends AddPermissionBase {

    public function __construct(string $player = "", string $playerPermission = "") {
        parent::__construct(self::ADD_PERMISSION, FlowItemCategory::PLAYER_PERMISSION, player: $player, playerPermission: $playerPermission);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $permission = $this->getPlayerPermission()->getString($source);
        $player = $this->getPlayer()->getOnlinePlayer($source);

        $player->addAttachment(Main::getInstance(), $permission, true);

        yield Await::ALL;
    }
}