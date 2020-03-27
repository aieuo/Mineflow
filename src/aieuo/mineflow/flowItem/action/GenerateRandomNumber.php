<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use pocketmine\entity\Entity;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\recipe\Recipe;

class GenerateRandomNumber extends TypeGetMathVariable {

    protected $id = self::GENERATE_RANDOM_NUMBER;

    protected $name = "action.generateRandomNumber.name";
    protected $detail = "action.generateRandomNumber.detail";
    protected $detailDefaultReplace = ["min", "max", "result"];

    /** @var string */
    protected $resultName = "random";
    /** @var string */
    private $min = "";
    /** @var string */
    private $max = "";

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

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $min = $origin->replaceVariables($this->getMin());
        $max = $origin->replaceVariables($this->getMax());
        $resultName = $origin->replaceVariables($this->getResultName());

        if (!is_numeric($min) or !is_numeric($max)) {
            throw new \UnexpectedValueException(Language::get("flowItem.error", [$this->getName(), ["flowItem.error.notNumber"]]));
        }

        $origin->addVariable(new NumberVariable(mt_rand((float)$min, (float)$max), $resultName));
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@action.generateRandomNumber.form.min", Language::get("form.example", ["0"]), $default[1] ?? $this->getMin()),
                new Input("@action.generateRandomNumber.form.max", Language::get("form.example", ["10"]), $default[2] ?? $this->getMax()),
                new Input("@action.calculate.form.result", Language::get("form.example", ["random"]), $default[3] ?? $this->getResultName()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[1] === "") $errors[] = ["@form.insufficient", 1];
        if ($data[2] === "") $errors[] = ["@form.insufficient", 2];
        if ($data[3] === "") $data[3] = "random";
        return ["status" => empty($errors), "contents" => [$data[1]], "cancel" => $data[2], "errors" => $errors];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[2])) throw new \OutOfBoundsException();
        $this->setMin($content[0]);
        $this->setMax($content[1]);
        $this->setResultName($content[2]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getMin(), $this->getMax(), $this->getResultName()];
    }
}