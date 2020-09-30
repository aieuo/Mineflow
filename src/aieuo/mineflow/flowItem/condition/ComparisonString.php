<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\mineflow\CancelToggle;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;

class ComparisonString extends FlowItem implements Condition {

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
    private $operator;
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

    public function execute(Recipe $origin) {
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
                throw new InvalidFlowValueException($this->getName(), Language::get("action.calculate.operator.unknown", [$operator]));
        }
        yield true;
        return $result;
    }

    public function getEditForm(): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@condition.comparisonNumber.form.value1", "10", $this->getValue1(), true),
                new Dropdown("@condition.comparisonNumber.form.operator", $this->operatorSymbols, $this->getOperator()),
                new ExampleInput("@condition.comparisonNumber.form.value2", "50", $this->getValue2(), false),
                new CancelToggle()
            ]);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], $data[2], $data[3]], "cancel" => $data[4]];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setValues($content[0], $content[2]);
        $this->setOperator($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getValue1(), $this->getOperator(), $this->getValue2()];
    }
}
