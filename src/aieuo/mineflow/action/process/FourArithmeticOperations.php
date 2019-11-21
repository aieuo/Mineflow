<?php

namespace aieuo\mineflow\action\process;

use pocketmine\entity\Entity;
use pocketmine\Player;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\action\process\Process;
use aieuo\mineflow\FormAPI\element\Dropdown;
use aieuo\mineflow\FormAPI\element\Toggle;
use aieuo\mineflow\variable\NumberVariable;

class FourArithmeticOperations extends Process {

    protected $id = self::FOUR_ARITHMETIC_OPERATIONS;

    protected $name = "@action.fourArithmeticOperations.name";
    protected $description = "@action.fourArithmeticOperations.description";
    protected $detail = "action.fourArithmeticOperations.detail";

    protected $category = Categories::CATEGORY_ACTION_CALCULATION;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    const ADDITION = 0;
    const SUBTRACTION = 1;
    const MULTIPLICATION = 2;
    const DIVISION = 3;

    /** @var string */
    private $value1;
    /** @var int */
    private $operator = self::ADDITION;
    /** @var string */
    private $value2;
    /** @var string */
    private $resultName = "result";

    private $operatorSymbols = ["+", "-", "*", "/"];

    public function __construct(string $value1 = "", int $operator = self::ADDITION, string $value2 = "", string $resultName = "result") {
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
        return Language::get($this->detail, [$this->getValue1(), $this->operatorSymbols[$this->getOperator()], $this->getValue2()]);
    }

    public function execute(?Entity $target, ?Recipe $origin = null): ?bool {
        if (!$this->isDataValid()) {
            $target->sendMessage(Language::get("invalid.contents", [$this->getName()]));
            return null;
        }
        if (!($origin instanceof Recipe)) {
            $target->sendMessage(Language::get("action.error", [Language::get("action.error.recipe"), $this->getName()]));
            return null;
        }

        $value1 = $this->getValue1();
        $value2 = $this->getValue2();
        $resultName = $this->getResultName();
        $operator = $this->getOperator();
        if ($origin instanceof Recipe) {
            $value1 = $origin->replaceVariables($value1);
            $value2 = $origin->replaceVariables($value2);
            $resultName = $origin->replaceVariables($resultName);
        }

        if (!is_numeric($value1)) {
            $target->sendMessage(Language::get("action.error", [$this->getName(), Language::get("action.fourArithmeticOperations.notNumber")]));
            return null;
        }
        if (!is_numeric($value2)) {
            $target->sendMessage(Language::get("action.error", [$this->getName(), Language::get("action.fourArithmeticOperations.notNumber")]));
            return null;
        }

        switch ($operator) {
            case self::ADDITION:
                $result = (float)$value1 + (float)$value2;
                break;
            case self::SUBTRACTION:
                $result = (float)$value1 - (float)$value2;
                break;
            case self::MULTIPLICATION:
                $result = (float)$value1 * (float)$value2;
                break;
            case self::DIVISION:
                if ((float)$value2 == 0) {
                    $target->sendMessage(Language::get("action.error", [$this->getName(), Language::get("variable.number.div.0")]));
                    return null;
                }
                $result = (float)$value1 / (float)$value2;
                break;
        }

        $origin->addVariable(new NumberVariable($resultName, $result));
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []) {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@action.fourArithmeticOperations.form.value1", Language::get("form.example", ["10"]), $default[1] ?? $this->getValue1()),
                new Dropdown("@action.fourArithmeticOperations.form.operator", $this->operatorSymbols, $default[2] ?? $this->getOperator()),
                new Input("@action.fourArithmeticOperations.form.value2", Language::get("form.example", ["50"]), $default[3] ?? $this->getValue2()),
                new Input("@action.calculate.form.result", Language::get("form.example", ["result"]), $default[4] ?? $this->getResultName()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $status = true;
        $errors = [];
        if ($data[1] === "") {
            $status = false;
            $errors[] = ["@form.insufficient", 1];
        }
        if ($data[3] === "") {
            $status = false;
            $errors[] = ["@form.insufficient", 3];
        }
        if ($data[4] === "") $data[4] = "result";
        return ["status" => $status, "contents" => [$data[1], $data[2], $data[3], $data[4]], "cancel" => $data[5], "errors" => $errors];
    }

    public function parseFromSaveData(array $content): ?Process {
        if (!isset($content[3])) return null;
        $this->setValues($content[0], $content[2]);
        $this->setOperator($content[1]);
        $this->setResultName($content[3]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getValue1(), $this->getOperator(), $this->getValue2(), $this->getResultName()];
    }
}