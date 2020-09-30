<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\mineflow\CancelToggle;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;

class StringLength extends FlowItem {

    protected $id = self::STRING_LENGTH;

    protected $name = "action.strlen.name";
    protected $detail = "action.strlen.detail";
    protected $detailDefaultReplace = ["string", "result"];

    protected $category = Category::STRING;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;
    protected $returnValueType = self::RETURN_VARIABLE_VALUE;

    /** @var string */
    private $value;
    /** @var string */
    private $resultName;

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

    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $value = $origin->replaceVariables($this->getValue());
        $resultName = $origin->replaceVariables($this->getResultName());

        $length = mb_strlen($value);
        $origin->addVariable(new NumberVariable($length, $resultName));
        yield true;
        return $length;
    }

    public function getEditForm(): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@action.strlen.form.value", "aieuo", $this->getValue(), true),
                new ExampleInput("@flowItem.form.resultVariableName", "length", $this->getResultName(), true),
                new CancelToggle()
            ]);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], $data[2]], "cancel" => $data[3], "errors" => []];
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
        return [new DummyVariable($this->getResultName(), DummyVariable::NUMBER)];
    }
}