<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;

abstract class AddXpBase extends SimpleAction {

    public function __construct(string $id, string $category = FlowItemCategory::PLAYER, string $player = "", int $xp = null) {
        parent::__construct($id, $category);

        $this->setArguments([
            PlayerArgument::create("player", $player),
            NumberArgument::create("xp", $xp)->example("10"),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->getArgument("player");
    }

    public function getXp(): NumberArgument {
        return $this->getArgument("xp");
    }
}