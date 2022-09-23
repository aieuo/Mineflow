<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NullVariable;
use aieuo\mineflow\variable\object\WorldVariable;
use pocketmine\Server;

class GetWorldByName extends FlowItem {

    protected string $id = self::GET_WORLD_BY_NAME;

    protected string $name = "action.getWorldByName.name";
    protected string $detail = "action.getWorldByName.detail";
    protected array $detailDefaultReplace = ["name", "result"];

    protected string $category = FlowItemCategory::WORLD;
    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    public function __construct(
        private string $worldName = "",
        private string $resultName = "world"
    ) {
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

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getWorldName(), $this->getResultName()]);
    }

    public function isDataValid(): bool {
        return $this->getWorldName() !== "" and $this->getResultName() !== "";
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $worldName = $source->replaceVariables($this->getWorldName());
        $result = $source->replaceVariables($this->getResultName());

        $world = Server::getInstance()->getWorldManager()->getWorldByName($worldName);

        $variable = $world === null ? new NullVariable() : new WorldVariable($world);
        $source->addVariable($result, $variable);
        yield true;
        return $this->getResultName();
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.getWorldByName.form.worldName", "world", $this->getWorldName(), true),
            new ExampleInput("@action.form.resultVariableName", "world", $this->getResultName(), true),
        ];
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
            $this->getResultName() => new DummyVariable(DummyVariable::WORLD)
        ];
    }
}
