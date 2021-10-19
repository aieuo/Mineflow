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

        $xp = $source->replaceVariables($this->getXp());
        $this->throwIfInvalidNumber($xp);

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        $new = $player->getXpLevel() + (int)$xp;
        if ($new < 0) $xp = -$player->getXpLevel();
        $player->addXpLevels((int)$xp);
        yield FlowItemExecutor::CONTINUE;
    }
}