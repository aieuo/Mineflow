<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\placeholder\EntityPlaceholder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;

abstract class SetHealthBase extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected EntityPlaceholder $entity;

    public function __construct(
        string $id,
        string $category = FlowItemCategory::ENTITY,
        string $entity = "",
        private string $health = ""
    ) {
        parent::__construct($id, $category);

        $this->entity = new EntityPlaceholder("entity", $entity);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->entity->getName(), "health"];
    }

    public function getDetailReplaces(): array {
        return [$this->entity->get(), $this->getHealth()];
    }

    public function getEntity(): EntityPlaceholder {
        return $this->entity;
    }

    public function setHealth(string $health): void {
        $this->health = $health;
    }

    public function getHealth(): string {
        return $this->health;
    }

    public function isDataValid(): bool {
        return $this->entity->isNotEmpty() and $this->health !== "";
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
           $this->entity->createFormElement($variables),
            new ExampleNumberInput("@action.setHealth.form.health", "20", $this->getHealth(), true, 1),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->entity->set($content[0]);
        $this->setHealth($content[1]);
    }

    public function serializeContents(): array {
        return [$this->entity->get(), $this->getHealth()];
    }
}
