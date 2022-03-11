<?php

namespace aieuo\mineflow\flowItem\action\common;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\StringVariable;

class GetDate extends FlowItem {

    protected string $name = "action.getDate.name";
    protected string $detail = "action.getDate.detail";
    protected array $detailDefaultReplace = ["format", "result"];

    protected string $returnValueType = self::RETURN_VARIABLE_VALUE;

    public function __construct(
        private string $format = "H:i:s",
        private string $resultName = "date"
    ) {
        parent::__construct(self::GET_DATE, FlowItemCategory::COMMON);
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

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getFormat(), $this->getResultName()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $format = $source->replaceVariables($this->getFormat());
        $resultName = $source->replaceVariables($this->getResultName());

        $date = date($format);
        $source->addVariable($resultName, new StringVariable($date));
        yield true;
        return $date;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.getDate.form.format", "H:i:s", $this->getFormat(), true),
            new ExampleInput("@action.form.resultVariableName", "date", $this->getResultName(), true),
        ];
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
