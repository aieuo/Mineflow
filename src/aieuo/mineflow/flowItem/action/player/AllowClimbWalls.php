<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\argument\BooleanArgument;
use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use SOFe\AwaitGenerator\Await;

class AllowClimbWalls extends SimpleAction {
    
    private PlayerArgument $player;
    private BooleanArgument $allow;

    public function __construct(string $player = "", bool $allow = true) {
        parent::__construct(self::ALLOW_CLIMB_WALLS, FlowItemCategory::PLAYER);

        $this->setArguments([
            $this->player = new PlayerArgument("player", $player),
            $this->allow = new BooleanArgument("allow", $allow),
        ]);
    }

    public function getDetailReplaces(): array {
        return [$this->player->get(), Language::get("action.allowFlight.".($this->allow->getBool() ? "allow" : "notAllow"))];
    }

    public function getPlayer(): PlayerArgument {
        return $this->player;
    }

    public function getAllow(): BooleanArgument {
        return $this->allow;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $player = $this->player->getOnlinePlayer($source);

        $player->setCanClimbWalls($this->allow->getBool());

        yield Await::ALL;
    }
}
