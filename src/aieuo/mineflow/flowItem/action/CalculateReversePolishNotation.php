<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\ExampleInput;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\CustomForm;

class CalculateReversePolishNotation extends FlowItem {

    protected $id = self::REVERSE_POLISH_NOTATION;

    protected $name = "action.calculateRPN.name";
    protected $detail = "action.calculateRPN.detail";
    protected $detailDefaultReplace = ["formula", "result"];

    protected $category = Category::MATH;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;
    protected $returnValueType = self::RETURN_VARIABLE_VALUE;

    /** @var string */
    private $formula;
    /** @var string */
    private $resultName;

    /* @var string */
    private $lastResult;

    public function __construct(string $formula = "", string $resultName = "result") {
        $this->formula = $formula;
        $this->resultName = $resultName;
    }

    public function setFormula(string $formula): self {
        $this->formula = $formula;
        return $this;
    }

    public function getFormula(): string {
        return $this->formula;
    }

    public function setResultName(string $name): self {
        $this->resultName = $name;
        return $this;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->getFormula() !== "" and $this->getResultName() !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getFormula(), $this->getResultName()]);
    }

    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $formula = $origin->replaceVariables($this->getFormula());
        $resultName = $origin->replaceVariables($this->getResultName());

        $stack = [];
        foreach (explode(" ", $formula) as $token) {
            if (is_numeric($token)) {
                $stack[] = (float)$token;
                continue;
            }

            $value2 = array_pop($stack);
            $value1 = array_pop($stack);
            switch ($token) {
                case '+':
                    $res = $value1 + $value2;
                    break;
                case '-':
                    $res = $value1 - $value2;
                    break;
                case '*':
                    $res = $value1 * $value2;
                    break;
                case '/':
                    $res = $value1 / $value2;
                    break;
                case '%':
                    $res = $value1 % $value2;
                    break;
                default:
                    throw new InvalidFlowValueException($this->getName(), Language::get("action.calculate.operator.unknown", [$token]));
            }
            $stack[] = $res;
        }
        $result = $stack[0];

        $this->lastResult = (string)$result;
        $origin->addVariable(new NumberVariable($result, $resultName));
        yield true;
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@action.calculateRPN.form.value", "1 2 + 3 -", $default[1] ?? $this->getFormula(), true),
                new ExampleInput("@flowItem.form.resultVariableName", "result", $default[2] ?? $this->getResultName(), true),
                new CancelToggle()
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], $data[2]], "cancel" => $data[3], "errors" => []];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setFormula($content[0]);
        $this->setResultName($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getFormula(), $this->getResultName()];
    }

    public function getReturnValue(): string {
        return $this->lastResult;
    }
}