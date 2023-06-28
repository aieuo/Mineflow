<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\argument\EntityArgument;
use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;

abstract class SetHealthBase extends SimpleAction {

    protected EntityArgument $entity;
    protected NumberArgument $health;

    public function __construct(string $id, string $category = FlowItemCategory::ENTITY, string $entity = "", int $health = null) {
        parent::__construct($id, $category);

        $this->setArguments([
            $this->entity = new EntityArgument("entity", $entity),
            $this->health = new NumberArgument("health", $health ?? "", "@action.setHealth.form.health", example: "20", min: 0),
        ]);
    }

    public function getEntity(): EntityArgument {
        return $this->entity;
    }

    public function getHealth(): NumberArgument {
        return $this->health;
    }
}
