<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\entity;

use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\base\ConditionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;

abstract class CheckEntityStateById extends FlowItem implements Condition {
    use ConditionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected NumberArgument $entityId;

    public function __construct(
        string $id,
        string $category = FlowItemCategory::ENTITY,
        string $entityId = "",
    ) {
        parent::__construct($id, $category);

        $this->entityId = new NumberArgument("id", $entityId, "@condition.isActiveEntity.form.entityId", example: "aieuo");
    }

    public function getDetailDefaultReplaces(): array {
        return ["id"];
    }

    public function getDetailReplaces(): array {
        return [$this->entityId->get()];
    }

    public function getEntityId(): NumberArgument {
        return $this->entityId;
    }

    public function isDataValid(): bool {
        return $this->getEntityId() !== null;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->entityId->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->entityId->set($content[0]);
    }

    public function serializeContents(): array {
        return [$this->entityId->get()];
    }
}
