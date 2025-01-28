<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\player;

use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\base\SimpleCondition;
use aieuo\mineflow\flowItem\FlowItemCategory;

abstract class OnlinePlayerCount extends SimpleCondition {

    public function __construct(
        string $id,
        string $category = FlowItemCategory::PLAYER,
        string $value = ""
    ) {
        parent::__construct($id, $category);

        $this->setArguments([
            NumberArgument::create("value", $value, "@condition.randomNumber.form.value")->example("5"),
        ]);
    }

    public function getValue(): NumberArgument {
        return $this->getArgument("value");
    }
}