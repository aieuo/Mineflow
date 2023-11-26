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

    public function __construct(string $player = "", bool $allow = true) {
        parent::__construct(self::ALLOW_FLIGHT, FlowItemCategory::PLAYER);
        $this->setPermissions([FlowItemPermission::CHEAT]);

        $this->setArguments([
            PlayerArgument::create("player", $player),
            BooleanArgument::create("allow", $allow, "@action.allowClimbWalls.form.allow")
                ->format(fn(bool $value) => Language::get("action.allowFlight.".($value ? "allow" : "notAllow"))),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->getArguments()[0];
    }

    public function getAllow(): BooleanArgument {
        return $this->getArguments()[1];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $player = $this->getPlayer()->getOnlinePlayer($source);

        $player->setAllowFlight($this->getAllow()->getBool());

        yield Await::ALL;
    }
}
