<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\formAPI\element\Dropdown;

class ComparisonNumber extends Condition {

    protected $id = self::COMPARISON_NUMBER;

    protected $name = "condition.comparisonNumber.name";
    protected $detail = "condition.comparisonNumber.detail";
    protected $detailDefaultReplace = ["value1", "operator", "value2"];

    protected $category = Category::SCRIPT;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    const EQUAL = 0;
    const NOT_EQUAL = 1;
    const GREATER = 2;
    const LESS = 3;
    const GREATER_EQUAL = 4;
    const LESS_EQUAL = 5;

    /** @var string */
    private $value1;
    /** @var int */
    private $operator = self::EQUAL;
    /** @var string */
    private $value2;

    /** @var array */
    private $operatorSymbols = ["==", "!=", ">", "<", ">=", "<="];

    public function __construct(string $value1 = null, int $operator = self::EQUAL, string $value2 = null) {
        $this->value1 = $value1;
        $this->operator = $operator;
        $this->value2 = $value2;
    }

    public function setValues(string $value1, string $value2): self {
        $this->value1 = $value1;
        $this->value2 = $value2;
        return $this;
    }

    public function getValue1(): ?string {
        return $this->value1;
    }

    public function getValue2(): ?string {
        return $this->value2;
    }

    public function setOperator(int $operator) {
        $this->operator = $operator;
    }

    public function getOperator(): int {
        return $this->operator;
    }

    public function isDataValid(): bool {
        return $this->value1 !== "" and $this->value2 !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getValue1(), $this->operatorSymbols[$this->getOperator()], $this->getValue2()]);
    }

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $value1 = $origin->replaceVariables($this->getValue1());
        $value2 = $origin->replaceVariables($this->getValue2());
        $operator = $this->getOperator();

        if (!is_numeric($value1) or !is_numeric($value2)) {
            throw new \UnexpectedValueException(Language::get("flowItem.error", [$this->getName(), ["flowItem.error.notNumber"]]));
        }

        $value1 = (float)$value1;
        $value2 = (float)$value2;
        switch ($operator) {
            case self::EQUAL:
                $result = $value1 === $value2;
                break;
            case self::NOT_EQUAL:
                $result = $value1 !== $value2;
                break;
            case self::GREATER:
                $result = $value1 > $value2;
                break;
            case self::LESS:
                $result = $value1 < $value2;
                break;
            case self::GREATER_EQUAL:
                $result = $value1 >= $value2;
                break;
            case self::LESS_EQUAL:
                $result = $value1 <= $value2;
                break;
            default:
                throw new \UnexpectedValueException(Language::get("flowItem.error", [$this->getName(), ["action.calculate.operator.unknown", [$operator]]]));
        }
        return $result;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@condition.comparisonNumber.form.value1", Language::get("form.example", ["10"]), $default[1] ?? $this->getValue1()),
                new Dropdown("@condition.comparisonNumber.form.operator", $this->operatorSymbols, $default[2] ?? $this->getOperator()),
                new Input("@condition.comparisonNumber.form.value2", Language::get("form.example", ["50"]), $default[3] ?? $this->getValue2()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        $containsVariable = Main::getVariableHelper()->containsVariable($data[1]);
        if ($data[1] === "") {
            $errors[] = ["@form.insufficient", 1];
        } elseif (!$containsVariable and !is_numeric($data[1])) {
            $errors[] = ["@flowItem.error.notNumber", 1];
        }
        $containsVariable = Main::getVariableHelper()->containsVariable($data[3]);
        if ($data[3] === "") {
            $errors[] = ["@form.insufficient", 3];
        } elseif (!$containsVariable and !is_numeric($data[3])) {
            $errors[] = ["@flowItem.error.notNumber", 3];
        }
        return ["status" => empty($errors), "contents" => [$data[1], $data[2], $data[3]], "cancel" => $data[4], "errors" => $errors];
    }

    public function loadSaveData(array $content): Condition {
        if (!isset($content[2])) throw new \OutOfBoundsException();

        $this->setValues($content[0], $content[2]);
        $this->setOperator($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getValue1(), $this->getOperator(), $this->getValue2()];
    }
}
