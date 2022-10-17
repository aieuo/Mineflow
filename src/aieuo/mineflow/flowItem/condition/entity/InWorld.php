<?php

namespace aieuo\mineflow\flowItem\condition\entity;

use aieuo\mineflow\flowItem\base\ConditionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\EntityVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use SOFe\AwaitGenerator\Await;

class InWorld extends FlowItem implements Condition, EntityFlowItem {
    use EntityFlowItemTrait;
    use ConditionNameWithMineflowLanguage;

    public function __construct(string $entity = "", private string $world = "") {
        parent::__construct(self::IN_WORLD, FlowItemCategory::ENTITY);

        $this->setEntityVariableName($entity);
    }

    public function getDetailDefaultReplaces(): array {
        return ["target", "world"];
    }

    public function getDetailReplaces(): array {
        return [$this->getEntityVariableName(), $this->getWorld()];
    }

    public function setWorld(string $world): void {
        $this->world = $world;
    }

    public function getWorld(): string {
        return $this->world;
    }

    public function isDataValid(): bool {
        return $this->getEntityVariableName() !== "" and $this->getWorld() !== "";
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $entity = $this->getOnlineEntity($source);
        $world = $source->replaceVariables($this->getWorld());

        yield Await::ALL;
        return $entity->getPosition()->getWorld()->getFolderName() === $world;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new EntityVariableDropdown($variables, $this->getEntityVariableName()),
            new ExampleInput("@action.createPosition.form.world", "world", $this->getWorld(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setEntityVariableName($content[0]);
        $this->setWorld($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getEntityVariableName(), $this->getWorld()];
    }
}
