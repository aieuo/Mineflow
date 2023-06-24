<?php

namespace aieuo\mineflow\flowItem\condition\entity;

use aieuo\mineflow\flowItem\base\ConditionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\argument\EntityArgument;
use pocketmine\entity\Human;
use SOFe\AwaitGenerator\Await;

class IsSneaking extends FlowItem implements Condition {
    use ConditionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private EntityArgument $entity;

    public function __construct(string $entity = "") {
        parent::__construct(self::IS_SNEAKING, FlowItemCategory::ENTITY);

        $this->entity = new EntityArgument("target", $entity);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->entity->getName()];
    }

    public function getDetailReplaces(): array {
        return [$this->entity->get()];
    }

    public function getEntity(): EntityArgument {
        return $this->entity;
    }

    public function isDataValid(): bool {
        return $this->entity->isNotEmpty();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $entity = $this->entity->getOnlineEntity($source);

        yield Await::ALL;
        return $entity instanceof Human and $entity->isSneaking();
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
           $this->entity->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        if (isset($content[0])) $this->entity->set($content[0]);
    }

    public function serializeContents(): array {
        return [$this->entity->get()];
    }
}
