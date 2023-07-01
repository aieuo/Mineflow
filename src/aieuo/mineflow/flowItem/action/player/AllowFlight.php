<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\argument\BooleanArgument;
use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\FlowItemPermission;
use aieuo\mineflow\utils\Language;
use SOFe\AwaitGenerator\Await;

class AllowFlight extends SimpleAction {

    private PlayerArgument $player;
    private BooleanArgument $allow;

    public function __construct(string $player = "", bool $allow = true) {
        parent::__construct(self::ALLOW_FLIGHT, FlowItemCategory::PLAYER);
        $this->setPermissions([FlowItemPermission::CHEAT]);

        $this->setArguments([
            $this->player = new PlayerArgument("player", $player),
            $this->allow = new BooleanArgument(
                "allow", $allow, "@action.allowClimbWalls.form.allow",
                toStringFormatter: fn(bool $value) => Language::get("action.allowFlight.".($value ? "allow" : "notAllow"))
            ),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->player;
    }

    public function getAllow(): BooleanArgument {
        return $this->allow;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $player = $this->player->getOnlinePlayer($source);

        $player->setAllowFlight($this->allow->getBool());

        yield Await::ALL;
    }
}
