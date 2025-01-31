<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\libs\_6b4cfdc0a11de6c9\SOFe\AwaitGenerator\Await;

class AddXpLevel extends AddXpBase {

    public function __construct(string $player = "", int $xp = null) {
        parent::__construct(self::ADD_XP_LEVEL, player: $player, xp: $xp);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $xp = $this->getXp()->getInt($source);
        $player = $this->getPlayer()->getOnlinePlayer($source);

        $new = $player->getXpManager()->getXpLevel() + $xp;
        if ($new < 0) $xp = -$player->getXpManager()->getXpLevel();
        $player->getXpManager()->addXpLevels($xp);

        yield Await::ALL;
    }
}