<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class AddXpLevel extends AddXpBase {

    public function __construct(string $player = "", int $xp = null) {
        parent::__construct(self::ADD_XP_LEVEL, player: $player, xp: $xp);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $xp = $this->xp->getInt($source);
        $player = $this->player->getOnlinePlayer($source);

        $new = $player->getXpManager()->getXpLevel() + $xp;
        if ($new < 0) $xp = -$player->getXpManager()->getXpLevel();
        $player->getXpManager()->addXpLevels($xp);

        yield Await::ALL;
    }
}
