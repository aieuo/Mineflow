<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class AddXpLevel extends AddXpProgress {

    protected string $id = self::ADD_XP_LEVEL;

    protected string $name = "action.addXpLevel.name";
    protected string $detail = "action.addXpLevel.detail";

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $xp = $this->getInt($source->replaceVariables($this->getXp()));
        $player = $this->getOnlinePlayer($source);

        $new = $player->getXpManager()->getXpLevel() + $xp;
        if ($new < 0) $xp = -$player->getXpManager()->getXpLevel();
        $player->getXpManager()->addXpLevels((int)$xp);
        yield FlowItemExecutor::CONTINUE;
    }
}