<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\PositionFlowItem;
use aieuo\mineflow\flowItem\base\PositionFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;

class GetDistance extends FlowItem implements PositionFlowItem {
    use PositionFlowItemTrait;

    protected $id = self::GET_DISTANCE;

    protected $name = "action.getDistance.name";
    protected $detail = "action.getDistance.detail";
    protected $detailDefaultReplace = ["pos1", "pos2", "result"];

    protected $category = Category::LEVEL;
    protected $returnValueType = self::RETURN_VARIABLE_VALUE;

    /** @var string */
    private $resultName;

    public function __construct(string $pos1 = "", string $pos2 = "", string $result = "distance") {
        $this->setPositionVariableName($pos1, "pos1");
        $this->setPositionVariableName($pos2, "pos2");
        $this->resultName = $result;
    }

    public function setResultName(string $resultName): void {
        $this->resultName = $resultName;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->getPositionVariableName("pos1") !== "" and $this->getPositionVariableName("pos2") !== "" and $this->resultName !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPositionVariableName("pos1"), $this->getPositionVariableName("pos2"), $this->getResultName()]);
    }

    public function execute(Recipe $origin): \Generator {
        $this->throwIfCannotExecute();

        $pos1 = $this->getPosition($origin, "pos1");
        $pos2 = $this->getPosition($origin, "pos2");
        $result = $origin->replaceVariables($this->getResultName());

        $distance = $pos1->distance($pos2);

        $origin->addVariable(new NumberVariable($distance, $result));
        yield true;
        return $distance;
    }

    public function getEditForm(array $variables = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@action.getDistance.form.pos1", "pos1", $this->getPositionVariableName("pos1"), true),
                new ExampleInput("@action.getDistance.form.pos2", "pos2", $this->getPositionVariableName("pos2"), true),
                new ExampleInput("@action.form.resultVariableName", "distance", $this->getResultName(), true),
                new CancelToggle()
            ]);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], $data[2], $data[3]], "cancel" => $data[4]];
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
        return [new DummyVariable($this->getResultName(), DummyVariable::NUMBER)];
    }
}