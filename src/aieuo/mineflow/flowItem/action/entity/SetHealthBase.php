<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\argument\EntityArgument;
use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;

abstract class SetHealthBase extends SimpleAction {

    public function __construct(string $id, string $category = FlowItemCategory::ENTITY, string $entity = "", int $health = null) {
        parent::__construct($id, $category);

        $this->setArguments([
            new EntityArgument("entity", $entity),
            new NumberArgument("health", $health ?? "", "@action.setHealth.form.health", example: "20", min: 0),
        ]);
    }

    public function getEntity(): EntityArgument {
        return $this->getArguments()[0];
    }

    public function getHealth(): NumberArgument {
        return $this->getArguments()[1];
    }
}
