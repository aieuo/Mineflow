<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\PositionObjectVariable;
use pocketmine\level\Position;
use pocketmine\Server;

class CreatePositionVariable extends FlowItem {

    protected $id = self::CREATE_POSITION_VARIABLE;

    protected $name = "action.createPositionVariable.name";
    protected $detail = "action.createPositionVariable.detail";
    protected $detailDefaultReplace = ["position", "x", "y", "z", "level"];

    protected $category = Category::LEVEL;
    protected $returnValueType = self::RETURN_VARIABLE_NAME;

    /** @var string */
    private $variableName;
    /** @var string */
    private $x;
    /** @var string */
    private $y;
    /* @var string */
    private $z;
    /* @var string */
    private $level;

    public function __construct(string $x = "", string $y = "", string $z = "", string $level = "{target.level.name}", string $name = "pos") {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
        $this->level = $level;
        $this->variableName = $name;
    }

    public function setVariableName(string $variableName): void {
        $this->variableName = $variableName;
    }

    public function getVariableName(): string {
        return $this->variableName;
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

    public function setLevel(string $level): void {
        $this->level = $level;
    }

    public function getLevel(): string {
        return $this->level;
    }

    public function isDataValid(): bool {
        return $this->variableName !== "" and $this->x !== "" and $this->y !== "" and $this->z !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getVariableName(), $this->getX(), $this->getY(), $this->getZ(), $this->getLevel()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $name = $source->replaceVariables($this->getVariableName());
        $x = $source->replaceVariables($this->getX());
        $y = $source->replaceVariables($this->getY());
        $z = $source->replaceVariables($this->getZ());
        $levelName = $source->replaceVariables($this->getLevel());
        $level = Server::getInstance()->getLevelByName($levelName);

        $this->throwIfInvalidNumber($x);
        $this->throwIfInvalidNumber($y);
        $this->throwIfInvalidNumber($z);
        if ($level === null) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.createPositionVariable.level.notFound"));
        }

        $position = new Position((float)$x, (float)$y, (float)$z, $level);

        $variable = new PositionObjectVariable($position, $name);
        $source->addVariable($variable);
        yield true;
        return $this->getVariableName();
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleNumberInput("@action.createPositionVariable.form.x", "0", $this->getX(), true),
            new ExampleNumberInput("@action.createPositionVariable.form.y", "100", $this->getY(), true),
            new ExampleNumberInput("@action.createPositionVariable.form.z", "16", $this->getZ(), true),
            new ExampleInput("@action.createPositionVariable.form.level", "{target.level}", $this->getLevel(), true),
            new ExampleInput("@action.form.resultVariableName", "pos", $this->getVariableName(), true),
        ];
    }

    public function parseFromFormData(array $data): array {
        return [$data[4], $data[0], $data[1], $data[2], $data[3]];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setVariableName($content[0]);
        $this->setX($content[1]);
        $this->setY($content[2]);
        $this->setZ($content[3]);
        $this->setLevel($content[4]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getVariableName(), $this->getX(), $this->getY(), $this->getZ(), $this->getLevel()];
    }

    public function getAddingVariables(): array {
        $pos = $this->getX().", ".$this->getY().", ".$this->getZ().", ".$this->getLevel();
        return [new DummyVariable($this->getVariableName(), DummyVariable::POSITION, $pos)];
    }
}