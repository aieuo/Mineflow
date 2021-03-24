<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\PositionFlowItem;
use aieuo\mineflow\flowItem\base\PositionFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\PositionVariableDropdown;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\BlockObjectVariable;
use pocketmine\level\Position;

class GetBlock extends FlowItem implements PositionFlowItem {
    use PositionFlowItemTrait;

    protected $id = self::GET_BLOCK;

    protected $name = "action.getBlock.name";
    protected $detail = "action.getBlock.detail";
    protected $detailDefaultReplace = ["position", "result"];

    protected $category = Category::LEVEL;
    protected $returnValueType = self::RETURN_VARIABLE_NAME;

    private $resultName;

    public function __construct(string $position = "", string $result = "block") {
        $this->setPositionVariableName($position);
        $this->resultName = $result;
    }

    public function setResultName(string $resultName): void {
        $this->resultName = $resultName;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPositionVariableName(), $this->getResultName()]);
    }

    public function isDataValid(): bool {
        return $this->getPositionVariableName() !== "" and $this->getResultName() !== "";
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $position = $this->getPosition($source);
        $result = $source->replaceVariables($this->getResultName());

        /** @var Position $position */
        $block = $position->level->getBlock($position);

        $variable = new BlockObjectVariable($block);
        $source->addVariable($result, $variable);
        yield true;
        return $this->getResultName();
    }

    public function getEditFormElements(array $variables): array {
        return [
            new PositionVariableDropdown($variables, $this->getPositionVariableName()),
            new ExampleInput("@action.form.resultVariableName", "block", $this->getResultName(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setPositionVariableName($content[0]);
        $this->setResultName($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPositionVariableName(), $this->getResultName()];
    }

    public function getAddingVariables(): array {
        return [new DummyVariable($this->getResultName(), DummyVariable::BLOCK)];
    }
}
