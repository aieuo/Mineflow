<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\player;

use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\base\SimpleCondition;
use aieuo\mineflow\flowItem\FlowItemCategory;

abstract class CheckPlayerState extends SimpleCondition {

    protected PlayerArgument $player;

    public function __construct(
        string $id,
        string $category = FlowItemCategory::PLAYER,
        string $player = ""
    ) {
        parent::__construct($id, $category);

        $this->setArguments([
            $this->player = new PlayerArgument("player", $player),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->player;
    }
}
