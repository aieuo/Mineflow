<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\PositionFlowItem;
use aieuo\mineflow\flowItem\base\PositionFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\mineflow\CancelToggle;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\recipe\Recipe;
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

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;
    protected $returnValueType = self::RETURN_VARIABLE_NAME;

    private $resultName;

    public function __construct(string $position = "pos", string $result = "block") {
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

    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $position = $this->getPosition($origin);
        $this->throwIfInvalidPosition($position);
        $result = $origin->replaceVariables($this->getResultName());

        /** @var Position $position */
        $block = $position->level->getBlock($position);

        $variable = new BlockObjectVariable($block, $result);
        $origin->addVariable($variable);
        yield true;
        return $this->getResultName();
    }

    public function getEditForm(): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@flowItem.form.target.position", "pos", $this->getPositionVariableName()),
                new ExampleInput("@flowItem.form.resultVariableName", "block", $this->getResultName()),
                new CancelToggle()
            ]);
    }

    public function parseFromFormData(array $data): array {
        if ($data[1] === "") $data[1] = "pos";
        if ($data[2] === "") $data[2] = "block";
        return ["contents" => [$data[1], $data[2]], "cancel" => $data[3], "errors" => []];
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
