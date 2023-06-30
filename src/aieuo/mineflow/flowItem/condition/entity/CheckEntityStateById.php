<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\entity;

use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\base\SimpleCondition;
use aieuo\mineflow\flowItem\FlowItemCategory;

abstract class CheckEntityStateById extends SimpleCondition {

    protected NumberArgument $entityId;

    public function __construct(
        string $id,
        string $category = FlowItemCategory::ENTITY,
        string $entityId = "",
    ) {
        parent::__construct($id, $category);

        $this->setArguments([
            $this->entityId = new NumberArgument("id", $entityId, "@condition.isActiveEntity.form.entityId", example: "aieuo"),
        ]);
    }

    public function getEntityId(): NumberArgument {
        return $this->entityId;
    }
}
