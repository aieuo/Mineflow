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
use pocketmine\entity\Living;
use SOFe\AwaitGenerator\Await;

class ClearAllEffect extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private EntityPlaceholder $entity;

    public function __construct(string $entity = "") {
        parent::__construct(self::CLEAR_ALL_EFFECT, FlowItemCategory::ENTITY);

        $this->entity = new EntityPlaceholder("entity", $entity);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->entity->getName()];
    }

    public function getDetailReplaces(): array {
        return [$this->entity->get()];
    }

    public function isDataValid(): bool {
        return $this->entity->isNotEmpty();
    }

    public function getEntity(): EntityPlaceholder {
        return $this->entity;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $entity = $this->entity->getOnlineEntity($source);

        if ($entity instanceof Living) {
            $entity->getEffects()->clear();
        }

        yield Await::ALL;
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
