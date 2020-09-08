<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\ExampleInput;
use aieuo\mineflow\formAPI\element\ExampleNumberInput;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\PositionObjectVariable;
use pocketmine\level\Position;
use pocketmine\Server;

class CreatePositionVariable extends FlowItem {

    protected $id = self::CREATE_POSITION_VARIABLE;

    protected $name = "action.createPositionVariable.name";
    protected $detail = "action.createPositionVariable.detail";
    protected $detailDefaultReplace = ["position", "x", "y", "z", "level"];

    protected $category = Category::LEVEL;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;
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

    public function setVariableName(string $variableName) {
        $this->variableName = $variableName;
    }

    public function getVariableName(): string {
        return $this->variableName;
    }

    public function setX(string $x) {
        $this->x = $x;
    }

    public function getX(): string {
        return $this->x;
    }

    public function setY(string $y) {
        $this->y = $y;
    }

    public function getY(): string {
        return $this->y;
    }

    public function setZ(string $z) {
        $this->z = $z;
    }

    public function getZ(): string {
        return $this->z;
    }

    public function setLevel(string $level) {
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

    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $name = $origin->replaceVariables($this->getVariableName());
        $x = $origin->replaceVariables($this->getX());
        $y = $origin->replaceVariables($this->getY());
        $z = $origin->replaceVariables($this->getZ());
        $levelName = $origin->replaceVariables($this->getLevel());
        $level = Server::getInstance()->getLevelByName($levelName);

        if (!is_numeric($x) or !is_numeric($y) or !is_numeric($z)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("flowItem.error.notNumber"));
        }
        if ($level === null) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.createPositionVariable.level.notFound"));
        }

        $position = new Position((float)$x, (float)$y, (float)$z, $level);

        $variable = new PositionObjectVariable($position, $name);
        $origin->addVariable($variable);
        yield true;
        return $this->getVariableName();
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleNumberInput("@action.createPositionVariable.form.x", "0", $default[1] ?? $this->getX(), true),
                new ExampleNumberInput("@action.createPositionVariable.form.y", "100", $default[2] ?? $this->getY(), true),
                new ExampleNumberInput("@action.createPositionVariable.form.z", "16", $default[3] ?? $this->getZ(), true),
                new ExampleInput("@action.createPositionVariable.form.level", "{target.level}", $default[4] ?? $this->getLevel(), true),
                new ExampleInput("@flowItem.form.resultVariableName", "pos", $default[5] ?? $this->getVariableName(), true),
                new CancelToggle()
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[5], $data[1], $data[2], $data[3], $data[4]], "cancel" => $data[6], "errors" => []];
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
}