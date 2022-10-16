<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\math;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\variable\NumberVariable;
use SOFe\AwaitGenerator\Await;

class GenerateRandomNumber extends TypeGetMathVariable {
    use ActionNameWithMineflowLanguage;

    protected string $resultName = "random";

    public function __construct(
        private string $min = "",
        private string $max = "",
        string         $resultName = "random"
    ) {
        parent::__construct(self::GENERATE_RANDOM_NUMBER, resultName: $resultName);
    }

    public function getDetailDefaultReplaces(): array {
        return ["min", "max", "result"];
    }

    public function getDetailReplaces(): array {
        return [$this->getMin(), $this->getMax(), $this->getResultName()];
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

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $min = $source->replaceVariables($this->getMin());
        $max = $source->replaceVariables($this->getMax());
        $resultName = $source->replaceVariables($this->getResultName());

        $this->throwIfInvalidNumber($min);
        $this->throwIfInvalidNumber($max);

        $rand = mt_rand((int)$min, (int)$max);
        $source->addVariable($resultName, new NumberVariable($rand));

        yield Await::ALL;
        return $rand;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.random.form.min", "0", $this->getMin(), true),
            new ExampleInput("@action.random.form.max", "10", $this->getMax(), true),
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
