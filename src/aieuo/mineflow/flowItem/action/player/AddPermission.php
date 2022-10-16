<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\Main;
use SOFe\AwaitGenerator\Await;

class AddPermission extends AddPermissionBase {

    public function __construct(string $player = "", string $playerPermission = "") {
        parent::__construct(self::ADD_PERMISSION, FlowItemCategory::PLAYER, player: $player, playerPermission: $playerPermission);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $permission = $source->replaceVariables($this->getPlayerPermission());

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        $player->addAttachment(Main::getInstance(), $permission, true);

        yield Await::ALL;
    }
}
