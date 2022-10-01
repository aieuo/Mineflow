<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\math;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\NumberVariable;

class GenerateRandomNumber extends TypeGetMathVariable {

    protected string $name = "action.generateRandomNumber.name";
    protected string $detail = "action.generateRandomNumber.detail";
    protected array $detailDefaultReplace = ["min", "max", "result"];

    protected string $resultName = "random";

    public function __construct(
        private string $min = "",
        private string $max = "",
        string         $resultName = "random"
    ) {
        parent::__construct(self::GENERATE_RANDOM_NUMBER, resultName: $resultName);
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
        $source->addVariable($resultName, new NumberVariable($rand));
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