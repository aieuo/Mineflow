<?php

namespace aieuo\mineflow\flowItem\condition\entity;

use aieuo\mineflow\flowItem\argument\EntityArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ConditionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use SOFe\AwaitGenerator\Await;

class InWorld extends FlowItem implements Condition {
    use ConditionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private EntityArgument $entity;
    private StringArgument $world;

    public function __construct(string $entity = "", string $world = "") {
        parent::__construct(self::IN_WORLD, FlowItemCategory::ENTITY);

        $this->entity = new EntityArgument("target", $entity);
        $this->world = new StringArgument("world", $world, "@action.createPosition.form.world", example: "world");
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->entity->getName(), "world"];
    }

    public function getDetailReplaces(): array {
        return [$this->entity->get(), $this->world->get()];
    }

    public function getEntity(): EntityArgument {
        return $this->entity;
    }

    public function getWorld(): StringArgument {
        return $this->world;
    }

    public function isDataValid(): bool {
        return $this->entity->isNotEmpty() and $this->world->isNotEmpty();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $entity = $this->entity->getOnlineEntity($source);
        $world = $this->world->getString($source);

        yield Await::ALL;
        return $entity->getPosition()->getWorld()->getFolderName() === $world;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
           $this->entity->createFormElement($variables),
           $this->world->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->entity->set($content[0]);
        $this->world->set($content[1]);
    }

    public function serializeContents(): array {
        return [$this->entity->get(), $this->world->get()];
    }
}
