<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\ExampleInput;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\StringVariable;

class GetDate extends FlowItem {

    protected $id = self::GET_DATE;

    protected $name = "action.getDate.name";
    protected $detail = "action.getDate.detail";
    protected $detailDefaultReplace = ["format", "result"];

    protected $category = Category::COMMON;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;
    protected $returnValueType = self::RETURN_VARIABLE_VALUE;

    /** @var string */
    private $format;
    /** @var string */
    private $resultName;

    public function __construct(string $format = "H:i:s", string $resultName = "date") {
        $this->setFormat($format);
        $this->setResultName($resultName);
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

    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $format = $origin->replaceVariables($this->getFormat());
        $resultName = $origin->replaceVariables($this->getResultName());

        $date = date($format);
        $origin->addVariable(new StringVariable($date, $resultName));
        yield true;
        return $date;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@action.getDate.form.format", "H:i:s", $default[1] ?? $this->getFormat(), true),
                new ExampleInput("@flowItem.form.resultVariableName", "date", $default[2] ?? $this->getResultName(), true),
                new CancelToggle()
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], $data[2]], "cancel" => $data[3], "errors" => []];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setFormat($content[0]);
        $this->setResultName($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getFormat(), $this->getResultName()];
    }
}