<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\entity;

use aieuo\mineflow\flowItem\base\ConditionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\argument\EntityArgument;

abstract class CheckEntityState extends FlowItem implements Condition {
    use ConditionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected EntityArgument $entity;

    public function __construct(
        string $id,
        string $category = FlowItemCategory::ENTITY,
        string $entity = "",
    ) {
        parent::__construct($id, $category);

        $this->entity = new EntityArgument("entity", $entity);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->entity->getName()];
    }

    public function getDetailReplaces(): array {
        return [$this->entity->get()];
    }

    public function isDataValid(): bool {
        return $this->entity->get() !== null;
    }

    public function getEntity(): EntityArgument {
        return $this->entity;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->entity->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->entity->set($content[0]);
    }

    public function serializeContents(): array {
        return [$this->entity->get()];
    }
}
