<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class AddXpLevel extends AddXpBase {

    public function __construct(string $player = "", string $xp = "") {
        parent::__construct(self::ADD_XP_LEVEL, player: $player, xp: $xp);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $xp = $source->replaceVariables($this->getXp());
        $this->throwIfInvalidNumber($xp);

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        $new = $player->getXpManager()->getXpLevel() + (int)$xp;
        if ($new < 0) $xp = -$player->getXpManager()->getXpLevel();
        $player->getXpManager()->addXpLevels((int)$xp);

        yield Await::ALL;
    }
}
