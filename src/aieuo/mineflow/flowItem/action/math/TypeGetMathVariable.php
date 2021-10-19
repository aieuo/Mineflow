<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\math;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;

abstract class TypeGetMathVariable extends FlowItem {

    protected array $detailDefaultReplace = ["result"];

    protected string $category = Category::MATH;

    protected string $resultName = "result";
    protected string $returnValueType = self::RETURN_VARIABLE_VALUE;

    public function __construct(?string $result = "") {
        $this->resultName = empty($result) ? $this->resultName : $result;
    }

    public function setResultName(string $name): self {
        $this->resultName = $name;
        return $this;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->getResultName() !== "";
    }

    public function getDetail(): string {
        return Language::get($this->detail, [$this->getResultName()]);
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.form.resultVariableName", "result", $this->getResultName(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        if (isset($content[0])) $this->setResultName($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getResultName()];
    }

    public function getAddingVariables(): array {
        return [
            $this->getResultName() => new DummyVariable(NumberVariable::class)
        ];
    }
}