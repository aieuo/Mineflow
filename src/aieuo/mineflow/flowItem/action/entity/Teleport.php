<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\argument\EntityArgument;
use aieuo\mineflow\flowItem\argument\PositionArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use SOFe\AwaitGenerator\Await;

class Teleport extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private PositionArgument $position;
    private EntityArgument $entity;

    public function __construct(string $entity = "", string $position = "") {
        parent::__construct(self::TELEPORT, FlowItemCategory::ENTITY);

        $this->entity = new EntityArgument("entity", $entity);
        $this->position = new PositionArgument("position", $position);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->entity->getName(), $this->position->getName()];
    }

    public function getDetailReplaces(): array {
        return [$this->entity->get(), $this->position->get()];
    }

    public function isDataValid(): bool {
        return $this->entity->isValid() and $this->position->isValid();
    }

    public function getEntity(): EntityArgument {
        return $this->entity;
    }

    public function getPosition(): PositionArgument {
        return $this->position;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $entity = $this->entity->getOnlineEntity($source);
        $position = $this->position->getPosition($source);

        $entity->teleport($position);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
           $this->entity->createFormElement($variables),
            $this->position->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->entity->set($content[0]);
        $this->position->set($content[1]);
    }

    public function serializeContents(): array {
        return [$this->entity->get(), $this->position->get()];
    }
}
