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
            EntityArgument::create("entity", $entity),
            NumberArgument::create("health", $health ?? "", "@action.setHealth.form.health")->min(0)->example("20"),
        ]);
    }

    public function getEntity(): EntityArgument {
        return $this->getArgument("entity");
    }

    public function getHealth(): NumberArgument {
        return $this->getArgument("health");
    }
}