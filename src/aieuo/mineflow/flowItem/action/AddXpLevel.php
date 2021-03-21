<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class AddXpLevel extends AddXpProgress {

    protected $id = self::ADD_XP_LEVEL;

    protected $name = "action.addXpLevel.name";
    protected $detail = "action.addXpLevel.detail";

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $xp = $source->replaceVariables($this->getXp());
        $this->throwIfInvalidNumber($xp);

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        $new = $player->getXpLevel() + (int)$xp;
        if ($new < 0) $xp = -$player->getXpLevel();
        $player->addXpLevels((int)$xp);
        yield true;
    }
}