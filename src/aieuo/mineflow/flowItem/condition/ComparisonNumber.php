<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;

class ComparisonNumber extends FlowItem implements Condition {

    protected $id = self::COMPARISON_NUMBER;

    protected $name = "condition.comparisonNumber.name";
    protected $detail = "condition.comparisonNumber.detail";
    protected $detailDefaultReplace = ["value1", "operator", "value2"];

    protected $category = Category::SCRIPT;

    public const EQUAL = 0;
    public const NOT_EQUAL = 1;
    public const GREATER = 2;
    public const LESS = 3;
    public const GREATER_EQUAL = 4;
    public const LESS_EQUAL = 5;

    /** @var string */
    private $value1;
    /** @var int */
    private $operator;
    /** @var string */
    private $value2;

    /** @var array */
    private $operatorSymbols = ["==", "!=", ">", "<", ">=", "<="];

    public function __construct(string $value1 = "", int $operator = self::EQUAL, string $value2 = "") {
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

    public function setOperator(int $operator): void {
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

    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $value1 = $origin->replaceVariables($this->getValue1());
        $value2 = $origin->replaceVariables($this->getValue2());
        $operator = $this->getOperator();

        if (!is_numeric($value1) or !is_numeric($value2)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.error.notNumber"));
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
                throw new InvalidFlowValueException($this->getName(), Language::get("action.calculate.operator.unknown", [$operator]));
        }
        yield true;
        return $result;
    }

    public function getEditForm(array $variables = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleNumberInput("@condition.comparisonNumber.form.value1", "10", $this->getValue1(), true),
                new Dropdown("@condition.comparisonNumber.form.operator", $this->operatorSymbols, $this->getOperator()),
                new ExampleNumberInput("@condition.comparisonNumber.form.value2", "50", $this->getValue2(), true),
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
