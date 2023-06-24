<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\argument\EntityArgument;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use SOFe\AwaitGenerator\Await;

class SetScale extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private EntityArgument $entity;

    public function __construct(string $entity = "", private string $scale = "") {
        parent::__construct(self::SET_SCALE, FlowItemCategory::ENTITY);

        $this->entity = new EntityArgument("entity", $entity);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->entity->getName(), "scale"];
    }

    public function getDetailReplaces(): array {
        return [$this->entity->get(), $this->getScale()];
    }

    public function getEntity(): EntityArgument {
        return $this->entity;
    }

    public function setScale(string $scale): void {
        $this->scale = $scale;
    }

    public function getScale(): string {
        return $this->scale;
    }

    public function isDataValid(): bool {
        return $this->entity->isNotEmpty() and $this->scale !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $scale = $this->getFloat($source->replaceVariables($this->getScale()), min: 0, exclude: [0]);
        $entity = $this->entity->getOnlineEntity($source);

        $entity->setScale($scale);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
           $this->entity->createFormElement($variables),
            new ExampleNumberInput("@action.setScale.form.scale", "1", $this->getScale(), true, 0, excludes: [0]),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->entity->set($content[0]);
        $this->setScale($content[1]);
    }

    public function serializeContents(): array {
        return [$this->entity->get(), $this->getScale()];
    }
}
