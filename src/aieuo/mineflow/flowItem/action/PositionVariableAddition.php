<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\PositionFlowItem;
use aieuo\mineflow\flowItem\base\PositionFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\formAPI\element\mineflow\PositionVariableDropdown;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\PositionObjectVariable;
use pocketmine\level\Position;

class PositionVariableAddition extends FlowItem implements PositionFlowItem {
    use PositionFlowItemTrait;

    protected $id = self::POSITION_VARIABLE_ADDITION;

    protected $name = "action.positionAddition.name";
    protected $detail = "action.positionAddition.detail";
    protected $detailDefaultReplace = ["position", "x", "y", "z", "result"];

    protected $category = Category::LEVEL;
    protected $returnValueType = self::RETURN_VARIABLE_NAME;

    /** @var string */
    private $x;
    /** @var string */
    private $y;
    /* @var string */
    private $z;
    /** @var string */
    private $resultName;

    public function __construct(string $name = "pos", string $x = "", string $y = "", string $z = "", string $result = "pos") {
        $this->setPositionVariableName($name);
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
        $this->resultName = $result;
    }

    public function setX(string $x): void {
        $this->x = $x;
    }

    public function getX(): string {
        return $this->x;
    }

    public function setY(string $y): void {
        $this->y = $y;
    }

    public function getY(): string {
        return $this->y;
    }

    public function setZ(string $z): void {
        $this->z = $z;
    }

    public function getZ(): string {
        return $this->z;
    }

    public function setResultName(string $name): void {
        $this->resultName = $name;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->getPositionVariableName() !== "" and $this->x !== "" and $this->y !== "" and $this->z !== "" and $this->resultName !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPositionVariableName(), $this->getX(), $this->getY(), $this->getZ(), $this->getResultName()]);
    }

    public function execute(Recipe $source): \Generator {
        $this->throwIfCannotExecute();

        $pos = $this->getPosition($source);

        $x = $source->replaceVariables($this->getX());
        $y = $source->replaceVariables($this->getY());
        $z = $source->replaceVariables($this->getZ());
        $name = $source->replaceVariables($this->getResultName());

        $this->throwIfInvalidNumber($x);
        $this->throwIfInvalidNumber($y);
        $this->throwIfInvalidNumber($z);

        $position = Position::fromObject($pos->add($x, $y, $z), $pos->getLevel());

        $variable = new PositionObjectVariable($position, $name);
        $source->addVariable($variable);
        yield true;
        return $this->getResultName();
    }

    public function getEditFormElements(array $variables): array {
        return [
            new PositionVariableDropdown($variables),
            new ExampleNumberInput("@action.positionAddition.form.x", "0", $this->getX(), true),
            new ExampleNumberInput("@action.positionAddition.form.y", "100", $this->getY(), true),
            new ExampleNumberInput("@action.positionAddition.form.z", "16", $this->getZ(), true),
            new ExampleInput("@action.form.resultVariableName", "pos", $this->getResultName(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setPositionVariableName($content[0]);
        $this->setX($content[1]);
        $this->setY($content[2]);
        $this->setZ($content[3]);
        $this->setResultName($content[4]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPositionVariableName(), $this->getX(), $this->getY(), $this->getZ(), $this->getResultName()];
    }

    public function getAddingVariables(): array {
        $desc = $this->getPositionVariableName()." + (".$this->getX().",".$this->getY().",".$this->getZ().")";
        return [new DummyVariable($this->getResultName(), DummyVariable::POSITION, $desc)];
    }
}