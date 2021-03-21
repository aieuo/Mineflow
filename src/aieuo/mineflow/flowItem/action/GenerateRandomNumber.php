<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\utils\Language;
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

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $min = $source->replaceVariables($this->getMin());
        $max = $source->replaceVariables($this->getMax());
        $resultName = $source->replaceVariables($this->getResultName());

        $this->throwIfInvalidNumber($min);
        $this->throwIfInvalidNumber($max);

        $rand = mt_rand((int)$min, (int)$max);
        $source->addVariable(new NumberVariable($rand, $resultName));
        yield true;
        return $rand;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.generateRandomNumber.form.min", "0", $this->getMin(), true),
            new ExampleInput("@action.generateRandomNumber.form.max", "10", $this->getMax(), true),
            new ExampleInput("@action.form.resultVariableName", "random", $this->getResultName(), true),
        ];
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
}