<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\element\mineflow\CancelToggle;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Dropdown;

class Calculate extends FlowItem {

    protected $id = self::CALCULATE;

    protected $name = "action.calculate.name";
    protected $detail = "action.calculate.detail";
    protected $detailDefaultReplace = ["value", "operator", "result"];

    protected $category = Category::MATH;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;
    protected $returnValueType = self::RETURN_VARIABLE_VALUE;

    const SQUARE = 0;
    const SQUARE_ROOT = 1;
    const FACTORIAL = 2;
    const CALC_ABS = 3;
    const CALC_LOG = 4;
    const CALC_SIN = 5;
    const CALC_COS = 6;
    const CALC_TAN = 7;
    const CALC_ASIN = 8;
    const CALC_ACOS = 9;
    const CALC_ATAN = 10;
    const CALC_DEG2RAD = 11;
    const CALC_RAD2DEG = 12;
    const CALC_FLOOR = 13;
    const CALC_ROUND = 14;
    const CALC_CEIL = 15;

    /** @var string */
    private $value;
    /** @var int */
    private $operator;
    /** @var string */
    private $resultName;

    private $operatorSymbols = ["x^2", "√x", "x!", "abs(x)", "log(x)", "sin(x)", "cos(x)", "tan(x)", "asin(x)", "acos(x)", "atan(x)", "deg2rad(x)", "rad2deg(x)", "floor(x)", "round(x)", "ceil(x)"];

    public function __construct(string $value = "", string $operator = null, string $resultName = "result") {
        $this->value = $value;
        $this->operator = (int)($operator ?? self::SQUARE);
        $this->resultName = $resultName;
    }

    public function setValue(string $value): self {
        $this->value = $value;
        return $this;
    }

    public function getValue(): string {
        return $this->value;
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
        return $this->getValue() !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getValue(), $this->operatorSymbols[$this->getOperator()], $this->resultName]);
    }

    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $value = $origin->replaceVariables($this->getValue());
        $resultName = $origin->replaceVariables($this->getResultName());
        $operator = $this->getOperator();

        $this->throwIfInvalidNumber($value);

        $value = (float)$value;
        switch ($operator) {
            case self::SQUARE:
                $result = $value * $value;
                break;
            case self::SQUARE_ROOT:
                $result = sqrt($value);
                break;
            case self::FACTORIAL:
                $result = 1;
                for ($i=abs($value); $i>1; $i--) {
                    $result *= $i;
                }
                break;
            case self::CALC_ABS:
                $result = abs($value);
                break;
            case self::CALC_LOG:
                $result = log10($value);
                break;
            case self::CALC_SIN:
                $result = sin($value);
                break;
            case self::CALC_COS:
                $result = cos($value);
                break;
            case self::CALC_TAN:
                $result = tan($value);
                break;
            case self::CALC_ASIN:
                $result = asin($value);
                break;
            case self::CALC_ACOS:
                $result = acos($value);
                break;
            case self::CALC_ATAN:
                $result = atan($value);
                break;
            case self::CALC_DEG2RAD:
                $result = deg2rad($value);
                break;
            case self::CALC_RAD2DEG:
                $result = rad2deg($value);
                break;
            case self::CALC_FLOOR:
                $result = floor($value);
                break;
            case self::CALC_ROUND:
                $result = round($value);
                break;
            case self::CALC_CEIL:
                $result = ceil($value);
                break;
            default:
                throw new InvalidFlowValueException($this->getName(), Language::get("action.calculate.operator.unknown", [$operator]));
        }

        $origin->addVariable(new NumberVariable($result, $resultName));
        yield true;
        return $result;
    }

    public function getEditForm(array $variables = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@action.calculate.form.value", "10", $this->getValue(), true),
                new Dropdown("@action.fourArithmeticOperations.form.operator", $this->operatorSymbols, $this->getOperator()),
                new ExampleInput("@flowItem.form.resultVariableName", "result", $this->getResultName(), true),
                new CancelToggle()
            ]);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], $data[2], $data[3]], "cancel" => $data[4]];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setValue($content[0]);
        $this->setOperator($content[1]);
        $this->setResultName($content[2]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getValue(), $this->getOperator(), $this->getResultName()];
    }

    public function getAddingVariables(): array {
        return [new DummyVariable($this->getResultName(), DummyVariable::NUMBER)];
    }
}