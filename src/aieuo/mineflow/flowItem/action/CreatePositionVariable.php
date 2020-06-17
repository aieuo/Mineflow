<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\Main;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\PositionObjectVariable;
use pocketmine\level\Position;
use pocketmine\Server;

class CreatePositionVariable extends Action {

    protected $id = self::CREATE_POSITION_VARIABLE;

    protected $name = "action.createPositionVariable.name";
    protected $detail = "action.createPositionVariable.detail";
    protected $detailDefaultReplace = ["position", "x", "y", "z", "level"];

    protected $category = Category::LEVEL;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;
    protected $returnValueType = self::RETURN_VARIABLE_NAME;

    /** @var string */
    private $variableName = "pos";
    /** @var string */
    private $x;
    /** @var string */
    private $y;
    /* @var string */
    private $z;
    /* @var string */
    private $level = "{target.level.name}";

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

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $name = $origin->replaceVariables($this->getVariableName());
        $x = $origin->replaceVariables($this->getX());
        $y = $origin->replaceVariables($this->getY());
        $z = $origin->replaceVariables($this->getZ());
        $levelName = $origin->replaceVariables($this->getLevel());
        $level = Server::getInstance()->getLevelByName($levelName);

        if (!is_numeric($x) or !is_numeric($y) or !is_numeric($z)) {
            throw new \UnexpectedValueException(Language::get("flowItem.error", [$this->getName(), ["flowItem.error.notNumber"]]));
        }
        if ($level === null) {
            throw new \UnexpectedValueException(Language::get("flowItem.error", [$this->getName(), ["action.createPositionVariable.level.notFound"]]));
        }

        $position = new Position((float)$x, (float)$y, (float)$z, $level);

        $variable = new PositionObjectVariable($position, $name);
        $origin->addVariable($variable);
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@action.createPositionVariable.form.x", Language::get("form.example", ["0"]), $default[1] ?? $this->getX()),
                new Input("@action.createPositionVariable.form.y", Language::get("form.example", ["100"]), $default[2] ?? $this->getY()),
                new Input("@action.createPositionVariable.form.z", Language::get("form.example", ["16"]), $default[3] ?? $this->getZ()),
                new Input("@action.createPositionVariable.form.level", Language::get("form.example", ["world"]), $default[4] ?? $this->getLevel()),
                new Input("@flowItem.form.resultVariableName", Language::get("form.example", ["pos"]), $default[5] ?? $this->getVariableName()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        $helper = Main::getVariableHelper();
        for ($i=1; $i<=3; $i++) {
            if ($data[$i] === "") {
                $errors[] = ["@form.insufficient", $i];
            } elseif (!$helper->containsVariable($data[$i]) and !is_numeric($data[$i])) {
                $errors[] = ["@flowItem.error.notNumber", $i];
            }
        }
        if ($data[4] === "") $data[4] = "{target.level.name}";
        if ($data[5] === "") $data[5] = "pos";
        return ["contents" => [$data[5], $data[1], $data[2], $data[3], $data[4]], "cancel" => $data[6], "errors" => $errors];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[4])) throw new \OutOfBoundsException();
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

    public function getReturnValue(): string {
        return $this->getVariableName();
    }
}