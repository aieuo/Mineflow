<?php

namespace aieuo\mineflow\flowItem\condition\entity;

use aieuo\mineflow\flowItem\base\ConditionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\placeholder\EntityPlaceholder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use SOFe\AwaitGenerator\Await;

class InWorld extends FlowItem implements Condition {
    use ConditionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private EntityPlaceholder $entity;

    public function __construct(string $entity = "", private string $world = "") {
        parent::__construct(self::IN_WORLD, FlowItemCategory::ENTITY);

        $this->entity = new EntityPlaceholder("target", $entity);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->entity->getName(), "world"];
    }

    public function getDetailReplaces(): array {
        return [$this->entity->get(), $this->getWorld()];
    }

    public function getEntity(): EntityPlaceholder {
        return $this->entity;
    }

    public function setWorld(string $world): void {
        $this->world = $world;
    }

    public function getWorld(): string {
        return $this->world;
    }

    public function isDataValid(): bool {
        return $this->entity->isNotEmpty() and $this->getWorld() !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $entity = $this->entity->getOnlineEntity($source);
        $world = $source->replaceVariables($this->getWorld());

        yield Await::ALL;
        return $entity->getPosition()->getWorld()->getFolderName() === $world;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
           $this->entity->createFormElement($variables),
            new ExampleInput("@action.createPosition.form.world", "world", $this->getWorld(), true),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->entity->set($content[0]);
        $this->setWorld($content[1]);
    }

    public function serializeContents(): array {
        return [$this->entity->get(), $this->getWorld()];
    }
}
