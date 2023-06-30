<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\entity;

use aieuo\mineflow\flowItem\argument\EntityArgument;
use aieuo\mineflow\flowItem\base\SimpleCondition;
use aieuo\mineflow\flowItem\FlowItemCategory;

abstract class CheckEntityState extends SimpleCondition {

    protected EntityArgument $entity;

    public function __construct(
        string $id,
        string $category = FlowItemCategory::ENTITY,
        string $entity = "",
    ) {
        parent::__construct($id, $category);

        $this->setArguments([
            $this->entity = new EntityArgument("entity", $entity),
        ]);
    }

    public function getEntity(): EntityArgument {
        return $this->entity;
    }
}
