<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\formAPI\Form;
use pocketmine\entity\Entity;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\formAPI\element\Dropdown;

class Calculate extends Action {

    protected $id = self::CALCULATE;

    protected $name = "action.calculate.name";
    protected $detail = "action.calculate.detail";
    protected $detailDefaultReplace = ["value", "operator", "result"];

    protected $category = Categories::CATEGORY_ACTION_CALCULATION;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;
    protected $returnValueType = self::RETURN_VARIABLE_NUMBER;

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

    /** @var string */
    private $value;
    /** @var int */
    private $operator = self::SQUARE;
    /** @var string */
    private $resultName = "result";

    private $operatorSymbols = ["x^2", "√x", "x!", "abs(x)", "log(x)", "sin(x)", "cos(x)", "tan(x)", "asin(x)", "acos(x)", "atan(x)"];

    public function __construct(string $value = "", int $operator = self::SQUARE, string $resultName = "result") {
        $this->value = $value;
        $this->operator = $operator;
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

    public function execute(Recipe $origin): bool {
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
            case self::CALC_ATAN :
                $result = atan($value);
                break;
            default:
                throw new \UnexpectedValueException(Language::get("flowItem.error", [$this->getName(), ["action.calculate.operator.unknown", [$operator]]]));
        }

        $origin->addVariable(new NumberVariable($result, $resultName));
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@action.calculate.form.value", Language::get("form.example", ["10"]), $default[1] ?? $this->getValue()),
                new Dropdown("@action.fourArithmeticOperations.form.operator", $this->operatorSymbols, $default[2] ?? $this->getOperator()),
                new Input("@action.calculate.form.result", Language::get("form.example", ["result"]), $default[3] ?? $this->getResultName()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[1] === "") {
            $errors[] = ["@form.insufficient", 1];
        }
        if ($data[3] === "") $data[3] = "result";
        return ["status" => empty($errors), "contents" => [$data[1], $data[2], $data[3]], "cancel" => $data[4], "errors" => $errors];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[2])) throw new \OutOfBoundsException();
        $this->setValue($content[0]);
        $this->setOperator($content[1]);
        $this->setResultName($content[2]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getValue(), $this->getOperator(), $this->getResultName()];
    }
}