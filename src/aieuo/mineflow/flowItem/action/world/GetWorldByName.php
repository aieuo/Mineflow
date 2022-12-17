<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NullVariable;
use aieuo\mineflow\variable\object\WorldVariable;
use pocketmine\Server;
use SOFe\AwaitGenerator\Await;

class GetWorldByName extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    public function __construct(
        private string $worldName = "",
        private string $resultName = "world"
    ) {
        parent::__construct(self::GET_WORLD_BY_NAME, FlowItemCategory::WORLD);
    }

    public function getDetailDefaultReplaces(): array {
        return ["name", "result"];
    }

    public function getDetailReplaces(): array {
        return [$this->getWorldName(), $this->getResultName()];
    }

    public function getWorldName(): string {
        return $this->worldName;
    }

    public function setWorldName(string $worldName): void {
        $this->worldName = $worldName;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function setResultName(string $resultName): void {
        $this->resultName = $resultName;
    }

    public function isDataValid(): bool {
        return $this->getWorldName() !== "" and $this->getResultName() !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $worldName = $source->replaceVariables($this->getWorldName());
        $result = $source->replaceVariables($this->getResultName());

        $world = Server::getInstance()->getWorldManager()->getWorldByName($worldName);

        $variable = $world === null ? new NullVariable() : new WorldVariable($world);
        $source->addVariable($result, $variable);

        yield Await::ALL;
        return $this->getResultName();
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new ExampleInput("@action.getWorldByName.form.worldName", "world", $this->getWorldName(), true),
            new ExampleInput("@action.form.resultVariableName", "world", $this->getResultName(), true),
        ]);
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setWorldName($content[0]);
        $this->setResultName($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getWorldName(), $this->getResultName()];
    }

    public function getAddingVariables(): array {
        return [
            $this->getResultName() => new DummyVariable(WorldVariable::class)
        ];
    }
}
