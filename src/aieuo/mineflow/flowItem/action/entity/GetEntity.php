<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\utils\EntityHolder;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\EntityVariable;
use aieuo\mineflow\variable\object\PlayerVariable;
use SOFe\AwaitGenerator\Await;

class GetEntity extends FlowItem {
    use ActionNameWithMineflowLanguage;

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    public function __construct(private string $entityId = "", private string $resultName = "entity") {
        parent::__construct(self::GET_ENTITY, FlowItemCategory::ENTITY);
    }

    public function getDetailDefaultReplaces(): array {
        return ["id", "result"];
    }

    public function getDetailReplaces(): array {
        return [$this->getKey(), $this->getResultName()];
    }

    public function setKey(string $name): self {
        $this->entityId = $name;
        return $this;
    }

    public function getKey(): string {
        return $this->entityId;
    }

    public function setResultName(string $name): self {
        $this->resultName = $name;
        return $this;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->getKey() !== "" and !empty($this->getResultName());
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $id = $source->replaceVariables($this->getKey());
        $resultName = $source->replaceVariables($this->getResultName());

        $this->throwIfInvalidNumber($id, 0);

        $entity = EntityHolder::findEntity((int)$id);
        if ($entity === null) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.getEntity.notFound", [$id]));
        }
        $source->addVariable($resultName, EntityVariable::fromObject($entity));

        yield Await::ALL;
        return $this->getResultName();
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.getEntity.form.target", "aieuo", $this->getKey(), true),
            new ExampleInput("@action.form.resultVariableName", "entity", $this->getResultName(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setKey($content[0]);
        $this->setResultName($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getKey(), $this->getResultName()];
    }

    public function getAddingVariables(): array {
        return [
            $this->getResultName() => new DummyVariable(PlayerVariable::class, $this->getKey())
        ];
    }
}
