<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\placeholder\EntityPlaceholder;
use aieuo\mineflow\flowItem\placeholder\PositionPlaceholder;
use pocketmine\entity\Living;
use SOFe\AwaitGenerator\Await;

class LookAt extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private PositionPlaceholder $position;
    private EntityPlaceholder $entity;

    public function __construct(string $entity = "", string $position = "") {
        parent::__construct(self::LOOK_AT, FlowItemCategory::ENTITY);

        $this->entity = new EntityPlaceholder("entity", $entity);
        $this->position = new PositionPlaceholder("position", $position);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->entity->getName(), $this->position->getName()];
    }

    public function getDetailReplaces(): array {
        return [$this->entity->get(), $this->position->get()];
    }

    public function getEntity(): EntityPlaceholder {
        return $this->entity;
    }

    public function getPosition(): PositionPlaceholder {
        return $this->position;
    }

    public function isDataValid(): bool {
        return $this->entity->isNotEmpty() and $this->position->isNotEmpty();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $entity = $this->entity->getOnlineEntity($source);
        $position = $this->position->getPosition($source);

        if ($entity instanceof Living) {
            $entity->lookAt($position);
        }

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
