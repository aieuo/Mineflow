<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\formAPI\element\Dropdown;

class Calculate2 extends Action {

    protected $id = self::CALCULATE2;

    protected $name = "action.calculate2.name";
    protected $detail = "action.calculate2.detail";
    protected $detailDefaultReplace = ["value1", "value2", "operator", "result"];

    protected $category = Category::MATH;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;
    protected $returnValueType = self::RETURN_VARIABLE_VALUE;

    const CALC_MIN = 0;
    const CALC_MAX = 1;
    const CALC_POW = 2;
    const CALC_LOG = 3;
    const CALC_HYPOT = 4;
    const CALC_ATAN2 = 5;
    const CALC_ROUND = 6;

    /** @var string */
    private $value1;
    /** @var string */
    private $value2;
    /** @var int */
    private $operator;
    /** @var string */
    private $resultName;

    private $operatorSymbols = ["min(x, y)", "max(x, y)", "x^y", "log_y(x)", "√(x^2 + y^2)", "atan2(x, y)", "round(x, y)"];

    /* @var string */
    private $lastResult;

    public function __construct(string $value1 = "", string $value2 = "", string $operator = null, string $resultName = "result") {
        $this->value1 = $value1;
        $this->value2 = $value2;
        $this->operator = (int)($operator ?? self::CALC_MIN);
        $this->resultName = $resultName;
    }

    public function setValue1(string $value1): self {
        $this->value1 = $value1;
        return $this;
    }

    public function getValue1(): string {
        return $this->value1;
    }

    public function setValue2(string $value2): self {
        $this->value2 = $value2;
        return $this;
    }

    public function getValue2(): string {
        return $this->value2;
    }

    public function setOperator(int $operator): self {
        $this->operator = $operator;
        return $this;
    }

    public function getOperator(): int {
        return $this->operator;
    }

    public function setResultName(string $name): self {
        $this->resultName = $name;
        return $this;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->getValue1() !== "" and $this->getValue2() !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getValue1(), $this->getValue2(), $this->operatorSymbols[$this->getOperator()], $this->resultName]);
    }

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $value1 = $origin->replaceVariables($this->getValue1());
        $value2 = $origin->replaceVariables($this->getValue2());
        $resultName = $origin->replaceVariables($this->getResultName());
        $operator = $this->getOperator();

        $this->throwIfInvalidNumber($value1);
        $this->throwIfInvalidNumber($value2);

        $value1 = (float)$value1;
        $value2 = (float)$value2;
        switch ($operator) {
            case self::CALC_MIN:
                $result = min($value1, $value2);
                break;
            case self::CALC_MAX:
                $result = max($value1, $value2);
                break;
            case self::CALC_POW:
                $result = pow($value1, $value2);
                break;
            case self::CALC_LOG:
                $result = log($value1, $value2);
                break;
            case self::CALC_HYPOT:
                $result = hypot($value1, $value2);
                break;
            case self::CALC_ATAN2:
                $result = atan2($value1, $value2);
                break;
            case self::CALC_ROUND:
                $result = round($value1, (int)$value2);
                break;
            default:
                throw new \UnexpectedValueException(Language::get("flowItem.error", [$this->getName(), ["action.calculate.operator.unknown", [$operator]]]));
        }

        $this->lastResult = (string)$result;
        $origin->addVariable(new NumberVariable($result, $resultName));
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@action.calculate2.form.value1", Language::get("form.example", ["10"]), $default[1] ?? $this->getValue1()),
                new Input("@action.calculate2.form.value2", Language::get("form.example", ["20"]), $default[2] ?? $this->getValue2()),
                new Dropdown("@action.fourArithmeticOperations.form.operator", $this->operatorSymbols, $default[3] ?? $this->getOperator()),
                new Input("@flowItem.form.resultVariableName", Language::get("form.example", ["result"]), $default[4] ?? $this->getResultName()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[1] === "") $errors[] = ["@form.insufficient", 1];
        if ($data[2] === "") $errors[] = ["@form.insufficient", 2];
        if ($data[3] === "") $data[3] = "result";
        return ["contents" => [$data[1], $data[2], $data[3], $data[4]], "cancel" => $data[5], "errors" => $errors];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[3])) throw new \OutOfBoundsException();
        $this->setValue1($content[0]);
        $this->setValue2($content[1]);
        $this->setOperator($content[2]);
        $this->setResultName($content[3]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getValue1(), $this->getValue2(), $this->getOperator(), $this->getResultName()];
    }

    public function getReturnValue(): string {
        return $this->lastResult;
    }
}