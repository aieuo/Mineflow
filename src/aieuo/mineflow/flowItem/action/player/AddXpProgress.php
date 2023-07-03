<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class AddXpProgress extends AddXpBase {

    public function __construct(string $player = "", int $xp = null) {
        parent::__construct(self::ADD_XP_PROGRESS, player: $player, xp: $xp);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $xp = $this->getXp()->getInt($source);
        $player = $this->getPlayer()->getOnlinePlayer($source);

        $new = $player->getXpManager()->getCurrentTotalXp() + $xp;
        if ($new < 0) $xp = -$player->getXpManager()->getCurrentTotalXp();
        $player->getXpManager()->addXp($xp);

        yield Await::ALL;
    }
}
