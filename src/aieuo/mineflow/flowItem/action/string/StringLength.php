<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\string;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;

class StringLength extends FlowItem {

    protected string $id = self::STRING_LENGTH;

    protected string $name = "action.strlen.name";
    protected string $detail = "action.strlen.detail";
    protected array $detailDefaultReplace = ["string", "result"];

    protected string $category = Category::STRING;
    protected string $returnValueType = self::RETURN_VARIABLE_VALUE;

    private string $value;
    private string $resultName;

    public function __construct(string $value = "", string $resultName = "length") {
        $this->value = $value;
        $this->resultName = $resultName;
    }

    public function setValue(string $value1): self {
        $this->value = $value1;
        return $this;
    }

    public function getValue(): string {
        return $this->value;
    }

    public function setResultName(string $name): self {
        $this->resultName = $name;
        return $this;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->getValue() !== "" and $this->getResultName() !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getValue(), $this->getResultName()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $value = $source->replaceVariables($this->getValue());
        $resultName = $source->replaceVariables($this->getResultName());

        $length = mb_strlen($value);
        $source->addVariable($resultName, new NumberVariable($length));
        yield true;
        return $length;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.strlen.form.value", "aieuo", $this->getValue(), true),
            new ExampleInput("@action.form.resultVariableName", "length", $this->getResultName(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setValue($content[0]);
        $this->setResultName($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getValue(), $this->getResultName()];
    }

    public function getAddingVariables(): array {
        return [
            $this->getResultName() => new DummyVariable(NumberVariable::class)
        ];
    }
}