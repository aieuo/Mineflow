<?php

namespace aieuo\mineflow\flowItem\action\common;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\StringVariable;
use SOFe\AwaitGenerator\Await;

class GetDate extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected string $returnValueType = self::RETURN_VARIABLE_VALUE;

    public function __construct(
        private string $format = "H:i:s",
        private string $resultName = "date"
    ) {
        parent::__construct(self::GET_DATE, FlowItemCategory::COMMON);
    }

    public function getDetailDefaultReplaces(): array {
        return ["format", "result"];
    }

    public function getDetailReplaces(): array {
        return [$this->getFormat(), $this->getResultName()];
    }

    public function setFormat(string $format): void {
        $this->format = $format;
    }

    public function getFormat(): string {
        return $this->format;
    }

    public function setResultName(string $resultName): void {
        $this->resultName = $resultName;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->getFormat() !== "" and $this->getResultName();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $format = $source->replaceVariables($this->getFormat());
        $resultName = $source->replaceVariables($this->getResultName());

        $date = date($format);
        $source->addVariable($resultName, new StringVariable($date));

        yield Await::ALL;
        return $date;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new ExampleInput("@action.getDate.form.format", "H:i:s", $this->getFormat(), true),
            new ExampleInput("@action.form.resultVariableName", "date", $this->getResultName(), true),
        ]);
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setFormat($content[0]);
        $this->setResultName($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getFormat(), $this->getResultName()];
    }

    public function getAddingVariables(): array {
        return [
            $this->getResultName() => new DummyVariable(StringVariable::class)
        ];
    }
}
