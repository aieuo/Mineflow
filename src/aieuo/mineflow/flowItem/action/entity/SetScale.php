<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\EntityVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\utils\Language;
use SOFe\AwaitGenerator\Await;

class SetScale extends FlowItem implements EntityFlowItem {
    use EntityFlowItemTrait;
    use ActionNameWithMineflowLanguage;

    public function __construct(string $entity = "", private string $scale = "") {
        parent::__construct(self::SET_SCALE, FlowItemCategory::ENTITY);

        $this->setEntityVariableName($entity);
    }

    public function getDetailDefaultReplaces(): array {
        return ["entity", "scale"];
    }

    public function getDetailReplaces(): array {
        return [$this->getEntityVariableName(), $this->getScale()];
    }

    public function setScale(string $scale): void {
        $this->scale = $scale;
    }

    public function getScale(): string {
        return $this->scale;
    }

    public function isDataValid(): bool {
        return $this->getEntityVariableName() !== "" and $this->scale !== "";
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $health = $source->replaceVariables($this->getScale());

        $this->throwIfInvalidNumber($health, 0, null);

        $entity = $this->getEntity($source);
        $this->throwIfInvalidEntity($entity);

        $entity->setScale((float)$health);

        yield Await::ALL;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new EntityVariableDropdown($variables, $this->getEntityVariableName()),
            new ExampleNumberInput("@action.setScale.form.scale", "1", $this->getScale(), true, 0, excludes: [0]),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setEntityVariableName($content[0]);
        $this->setScale($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getEntityVariableName(), $this->getScale()];
    }
}
