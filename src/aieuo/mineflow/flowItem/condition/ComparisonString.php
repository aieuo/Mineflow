<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\formAPI\element\Dropdown;

class ComparisonString extends Condition {

    protected $id = self::COMPARISON_STRING;

    protected $name = "condition.comparisonString.name";
    protected $detail = "condition.comparisonString.detail";
    protected $detailDefaultReplace = ["value1", "operator", "value2"];

    protected $category = Category::SCRIPT;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    const EQUALS = 0;
    const NOT_EQUALS = 1;
    const CONTAINS = 2;
    const NOT_CONTAINS = 3;
    const STARTS_WITH = 4;
    const ENDS_WITH = 5;

    /** @var string */
    private $value1;
    /** @var int */
    private $operator = self::EQUALS;
    /** @var string */
    private $value2;

    /** @var array */
    private $operatorSymbols = ["==", "!=", "contains", "not contains", "starts with", "ends with"];

    public function __construct(string $value1 = "", string $operator = null, string $value2 = "") {
        $this->value1 = $value1;
        $this->operator = (int)($operator ?? self::EQUALS);
        $this->value2 = $value2;
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

    public function setOperator(int $operator) {
        $this->operator = $operator;
    }

    public function getOperator(): int {
        return $this->operator;
    }

    public function isDataValid(): bool {
        return $this->value1 !== "";
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

        switch ($operator) {
            case self::EQUALS:
                $result = $value1 === $value2;
                break;
            case self::NOT_EQUALS:
                $result = $value1 !== $value2;
                break;
            case self::CONTAINS:
                $result = strpos($value1, $value2) !== false;
                break;
            case self::NOT_CONTAINS:
                $result = strpos($value1, $value2) === false;
                break;
            case self::STARTS_WITH:
                $result = strpos($value1, $value2) === 0;
                break;
            case self::ENDS_WITH:
                $lenDiff = strlen($value1) - strlen($value2);
                $result = ($lenDiff < 0) ? false : strpos($value1, $value2, $lenDiff) !== false;
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
        if ($data[1] === "") {
            $errors[] = ["@form.insufficient", 1];
        }
        return ["contents" => [$data[1], $data[2], $data[3]], "cancel" => $data[4], "errors" => $errors];
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
