<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\recipe\Recipe;

class AddXpLevel extends AddXpProgress {

    protected $id = self::ADD_XP_LEVEL;

    protected $name = "action.addXpLevel.name";
    protected $detail = "action.addXpLevel.detail";

    public function execute(Recipe $origin): \Generator {
        $this->throwIfCannotExecute();

        $xp = $origin->replaceVariables($this->getXp());
        $this->throwIfInvalidNumber($xp);

        $player = $this->getPlayer($origin);
        $this->throwIfInvalidPlayer($player);

        $new = $player->getXpLevel() + (int)$xp;
        if ($new < 0) $xp = -$player->getXpLevel();
        $player->addXpLevels((int)$xp);
        yield true;
    }
}