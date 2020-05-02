<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\variable\StringVariable;

class StringCalc extends Action {

    protected $id = self::EDIT_STRING;

    protected $name = "action.stringCalc.name";
    protected $detail = "action.stringCalc.detail";
    protected $detailDefaultReplace = ["value1", "operator", "value2", "result"];

    protected $category = Category::MATH;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;
    protected $returnValueType = self::RETURN_VARIABLE_VALUE;

    const TYPE_JOIN = "join";
    const TYPE_DELETE = "delete";
    const TYPE_REPEAT = "repeat";
    const TYPE_SPLIT = "split";

    /** @var string[] */
    private $operators = [
        self::TYPE_JOIN,
        self::TYPE_DELETE,
        self::TYPE_REPEAT,
        self::TYPE_SPLIT,
    ];

    /** @var string */
    private $value1;
    /** @var string */
    private $operator = self::TYPE_JOIN;
    /** @var string */
    private $value2;
    /** @var string */
    private $resultName = "result";

    /* @var string */
    private $lastResult;

    public function __construct(string $value1 = "", string $operator = self::TYPE_JOIN, string $value2 = "", string $resultName = "result") {
        $this->value1 = $value1;
        $this->operator = $operator;
        $this->value2 = $value2;
        $this->resultName = $resultName;
    }

    public function setValues(string $value1, string $value2): self {
        $this->value1 = $value1;
        $this->value2 = $value2;
        return $this;
    }

    public function getValue1(): string {
        return $this->value1;
    }

    public function getValue2(): string {
        return $this->value2;
    }

    public function setOperator(string $operator): self {
        $this->operator = $operator;
        return $this;
    }

    public function getOperator(): string {
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
        return $this->getValue1() !== "" and $this->getValue2() !== "" and $this->getOperator() !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getValue1(), ["action.stringCalc.".$this->getOperator()], $this->getValue2(), $this->getResultName()]);
    }

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $value1 = $origin->replaceVariables($this->getValue1());
        $value2 = $origin->replaceVariables($this->getValue2());
        $resultName = $origin->replaceVariables($this->getResultName());
        $operator = $this->getOperator();

        switch ($operator) {
            case self::TYPE_JOIN:
                $result = new StringVariable($value1.$value2, $resultName);
                break;
            case self::TYPE_DELETE:
                $result = new StringVariable(str_replace($value2, "", $value1), $resultName);
                break;
            case self::TYPE_REPEAT:
                $this->throwIfInvalidNumber($value2, 1);
                $result = new StringVariable(str_repeat($value1, (int)$value2), $resultName);
                break;
            case self::TYPE_SPLIT:
                $result = new ListVariable(array_map(function (string $str) {
                    return new StringVariable($str);
                }, explode($value2, $value1)), $resultName);
                break;
            default:
                throw new \UnexpectedValueException(Language::get("flowItem.error", [$this->getName(), ["action.calculate.operator.unknown", [$operator]]]));
        }

        $this->lastResult = (string)$result;
        $origin->addVariable($result);
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@action.fourArithmeticOperations.form.value1", Language::get("form.example", ["10"]), $default[1] ?? $this->getValue1()),
                new Dropdown("@action.fourArithmeticOperations.form.operator", array_map(function (string $type) {
                    return Language::get("action.stringCalc.".$type);
                }, array_values($this->operators)), $default[2] ?? array_keys($this->operators, $this->getOperator())[0]),
                new Input("@action.fourArithmeticOperations.form.value2", Language::get("form.example", ["50"]), $default[3] ?? $this->getValue2()),
                new Input("@action.calculate.form.result", Language::get("form.example", ["result"]), $default[4] ?? $this->getResultName()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[1] === "") {
            $errors[] = ["@form.insufficient", 1];
        }
        if ($data[3] === "") {
            $errors[] = ["@form.insufficient", 3];
        }
        if ($data[4] === "") $data[4] = "result";
        return ["status" => empty($errors), "contents" => [$data[1], $data[2], $data[3], $data[4]], "cancel" => $data[5], "errors" => $errors];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[3])) throw new \OutOfBoundsException();
        $this->setValues($content[0], $content[2]);
        $this->setOperator($content[1]);
        $this->setResultName($content[3]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getValue1(), $this->getOperator(), $this->getValue2(), $this->getResultName()];
    }

    public function getReturnValue(): string {
        return $this->lastResult;
    }
}