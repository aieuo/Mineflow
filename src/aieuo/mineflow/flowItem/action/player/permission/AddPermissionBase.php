<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player\permission;

use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemPermission;

abstract class AddPermissionBase extends SimpleAction {

    protected PlayerArgument $player;
    protected StringArgument $playerPermission;

    public function __construct(
        string $id,
        string $category = FlowItemCategory::PLAYER_PERMISSION,
        string $player = "",
        string $playerPermission = ""
    ) {
        parent::__construct($id, $category);
        $this->setPermissions([FlowItemPermission::PERMISSION]);

        $this->setArguments([
            $this->player = new PlayerArgument("player", $player),
            $this->playerPermission = new StringArgument("permission", $playerPermission, "@condition.hasPermission.form.permission", example: "mineflow.customcommand.op"),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->player;
    }
}
