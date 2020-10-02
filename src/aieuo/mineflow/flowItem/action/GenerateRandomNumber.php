<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\mineflow\CancelToggle;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;

class GenerateRandomNumber extends TypeGetMathVariable {

    protected $id = self::GENERATE_RANDOM_NUMBER;

    protected $name = "action.generateRandomNumber.name";
    protected $detail = "action.generateRandomNumber.detail";
    protected $detailDefaultReplace = ["min", "max", "result"];

    /** @var string */
    protected $resultName = "random";
    /** @var string */
    private $min;
    /** @var string */
    private $max;

    public function __construct(string $min = "", string $max = "", string $result = null) {
        $this->min = $min;
        $this->max = $max;
        parent::__construct($result);
    }

    public function setMin(string $min): self {
        $this->min = $min;
        return $this;
    }

    public function getMin(): string {
        return $this->min;
    }

    public function setMax(string $max): self {
        $this->max = $max;
        return $this;
    }

    public function getMax(): string {
        return $this->max;
    }

    public function isDataValid(): bool {
        return $this->min !== "" and $this->max !== "" and $this->getResultName() !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getMin(), $this->getMax(), $this->getResultName()]);
    }

    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $min = $origin->replaceVariables($this->getMin());
        $max = $origin->replaceVariables($this->getMax());
        $resultName = $origin->replaceVariables($this->getResultName());

        if (!is_numeric($min) or !is_numeric($max)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("flowItem.error.notNumber"));
        }

        $rand = mt_rand((int)$min, (int)$max);
        $origin->addVariable(new NumberVariable($rand, $resultName));
        yield true;
        return $rand;
    }

    public function getEditForm(array $variables = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@action.generateRandomNumber.form.min", "0", $this->getMin(), true),
                new ExampleInput("@action.generateRandomNumber.form.max", "10", $this->getMax(), true),
                new ExampleInput("@flowItem.form.resultVariableName", "random", $this->getResultName(), true),
                new CancelToggle()
            ]);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], $data[2], $data[3]], "cancel" => $data[4]];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setMin($content[0]);
        $this->setMax($content[1]);
        $this->setResultName($content[2]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getMin(), $this->getMax(), $this->getResultName()];
    }

    public function getAddingVariables(): array {
        return [new DummyVariable($this->getResultName(), DummyVariable::NUMBER)];
    }
}