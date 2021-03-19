<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;

class Calculate extends FlowItem {

    protected $id = self::CALCULATE;

    protected $name = "action.calculate.name";
    protected $detail = "action.calculate.detail";
    protected $detailDefaultReplace = ["value", "operator", "result"];

    protected $category = Category::MATH;
    protected $returnValueType = self::RETURN_VARIABLE_VALUE;

    public const SQUARE = 0;
    public const SQUARE_ROOT = 1;
    public const FACTORIAL = 2;
    public const CALC_ABS = 3;
    public const CALC_LOG = 4;
    public const CALC_SIN = 5;
    public const CALC_COS = 6;
    public const CALC_TAN = 7;
    public const CALC_ASIN = 8;
    public const CALC_ACOS = 9;
    public const CALC_ATAN = 10;
    public const CALC_DEG2RAD = 11;
    public const CALC_RAD2DEG = 12;
    public const CALC_FLOOR = 13;
    public const CALC_ROUND = 14;
    public const CALC_CEIL = 15;

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

    public function execute(Recipe $origin): \Generator {
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

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.calculate.form.value", "10", $this->getValue(), true),
            new Dropdown("@action.fourArithmeticOperations.form.operator", $this->operatorSymbols, $this->getOperator()),
            new ExampleInput("@action.form.resultVariableName", "result", $this->getResultName(), true),
        ];
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