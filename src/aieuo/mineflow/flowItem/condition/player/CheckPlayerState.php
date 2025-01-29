<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\player;

use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\base\SimpleCondition;
use aieuo\mineflow\flowItem\FlowItemCategory;

abstract class CheckPlayerState extends SimpleCondition {

    public function __construct(
        string $id,
        string $category = FlowItemCategory::PLAYER,
        string $player = ""
    ) {
        parent::__construct($id, $category);

        $this->setArguments([
            PlayerArgument::create("player", $player),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->getArgument("player");
    }
}