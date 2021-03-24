<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\base\PositionFlowItem;
use aieuo\mineflow\flowItem\base\PositionFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\PositionVariableDropdown;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\PositionObjectVariable;
use pocketmine\level\Position;

class GenerateRandomPosition extends FlowItem implements PositionFlowItem {
    use PositionFlowItemTrait;

    protected $id = self::GENERATE_RANDOM_POSITION;

    protected $name = "action.generateRandomPosition.name";
    protected $detail = "action.generateRandomPosition.detail";
    protected $detailDefaultReplace = ["min", "max", "result"];

    /** @var string */
    private $resultName;

    protected $category = Category::LEVEL;
    protected $returnValueType = self::RETURN_VARIABLE_NAME;

    public function __construct(string $min = "", string $max = "", string $result = "position") {
        $this->setPositionVariableName($min, "pos1");
        $this->setPositionVariableName($max, "pos2");
        $this->resultName = $result;
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

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPositionVariableName("pos1"), $this->getPositionVariableName("pos2"), $this->getResultName()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $pos1 = $this->getPosition($source, "pos1");
        $pos2 = $this->getPosition($source, "pos2");
        $resultName = $source->replaceVariables($this->getResultName());

        if ($pos1->getLevelNonNull()->getFolderName() !== $pos2->getLevelNonNull()->getFolderName()) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.position.world.different"));
        }

        $x = mt_rand(min($pos1->x, $pos2->x), max($pos1->x, $pos2->x));
        $y = mt_rand(min($pos1->y, $pos2->y), max($pos1->y, $pos2->y));
        $z = mt_rand(min($pos1->z, $pos2->z), max($pos1->z, $pos2->z));
        $rand = new Position($x, $y, $z, $pos1->getLevelNonNull());
        $source->addVariable($resultName, new PositionObjectVariable($rand));
        yield true;
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
        return [new DummyVariable($this->getResultName(), DummyVariable::POSITION)];
    }
}