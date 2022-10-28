<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\PositionFlowItem;
use aieuo\mineflow\flowItem\base\PositionFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\PositionVariableDropdown;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\PositionVariable;
use pocketmine\world\Position;
use SOFe\AwaitGenerator\Await;

class GenerateRandomPosition extends FlowItem implements PositionFlowItem {
    use PositionFlowItemTrait;
    use ActionNameWithMineflowLanguage;

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    public function __construct(
        string         $min = "",
        string         $max = "",
        private string $resultName = "position"
    ) {
        parent::__construct(self::GENERATE_RANDOM_POSITION, FlowItemCategory::WORLD);

        $this->setPositionVariableName($min, "pos1");
        $this->setPositionVariableName($max, "pos2");
    }

    public function getDetailDefaultReplaces(): array {
        return ["min", "max", "result"];
    }

    public function getDetailReplaces(): array {
        return [$this->getPositionVariableName("pos1"), $this->getPositionVariableName("pos2"), $this->getResultName()];
    }

    public function setResultName(string $name): self {
        $this->resultName = $name;
        return $this;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->getResultName() !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $pos1 = $this->getPosition($source, "pos1");
        $pos2 = $this->getPosition($source, "pos2");
        $resultName = $source->replaceVariables($this->getResultName());

        if ($pos1->getWorld()->getFolderName() !== $pos2->getWorld()->getFolderName()) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.position.world.different"));
        }

        $x = mt_rand((int)min($pos1->x, $pos2->x), (int)max($pos1->x, $pos2->x));
        $y = mt_rand((int)min($pos1->y, $pos2->y), (int)max($pos1->y, $pos2->y));
        $z = mt_rand((int)min($pos1->z, $pos2->z), (int)max($pos1->z, $pos2->z));
        $rand = new Position($x, $y, $z, $pos1->getWorld());
        $source->addVariable($resultName, new PositionVariable($rand));

        yield Await::ALL;
        return $this->getResultName();
    }

    public function getEditFormElements(array $variables): array {
        return [
            new PositionVariableDropdown($variables, $this->getPositionVariableName("pos1"), "@action.form.target.position 1"),
            new PositionVariableDropdown($variables, $this->getPositionVariableName("pos2"), "@action.form.target.position 2"),
            new ExampleInput("@action.form.resultVariableName", "position", $this->getResultName(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setPositionVariableName($content[0], "pos1");
        $this->setPositionVariableName($content[1], "pos2");
        $this->setResultName($content[2]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPositionVariableName("pos1"), $this->getPositionVariableName("pos2"), $this->getResultName()];
    }

    public function getAddingVariables(): array {
        return [
            $this->getResultName() => new DummyVariable(PositionVariable::class)
        ];
    }
}
