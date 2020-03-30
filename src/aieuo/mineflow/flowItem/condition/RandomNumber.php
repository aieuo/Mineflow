<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\Main;
use aieuo\mineflow\utils\Language;
use pocketmine\entity\Entity;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;
use pocketmine\Player;

class RandomNumber extends Condition {

    protected $id = self::RANDOM_NUMBER;

    protected $name = "condition.randomNumber.name";
    protected $detail = "condition.randomNumber.detail";
    protected $detailDefaultReplace = ["min", "max", "value"];

    protected $category = Categories::CATEGORY_CONDITION_COMMON;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;
    protected $returnValueType = self::RETURN_NONE;

    /** @var string */
    private $min;
    /** @var string */
    private $max;
    /** @var string */
    private $value;

    public function __construct(string $min = "", string $max = "", string $value = "") {
        $this->min = $min;
        $this->max = $max;
        $this->value = $value;
    }

    public function setMin(string $min): void {
        $this->min = $min;
    }

    public function getMin(): string {
        return $this->min;
    }

    public function setMax(string $max): void {
        $this->max = $max;
    }

    public function getMax(): string {
        return $this->max;
    }

    public function setValue(string $value): void {
        $this->value = $value;
    }

    public function getValue(): string {
        return $this->value;
    }

    public function isDataValid(): bool {
        return $this->min !== "" and $this->max !== "" and $this->value !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getMin(), $this->getMax(), $this->getValue()]);
    }

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $min = $origin->replaceVariables($this->getMin());
        $max = $origin->replaceVariables($this->getMax());
        $value = $origin->replaceVariables($this->getValue());

        if (!is_numeric($min) or !is_numeric($max) or !is_numeric($value)) {
            throw new \UnexpectedValueException(Language::get("flowItem.error", [$this->getName(), ["flowItem.error.notNumber"]]));
        }

        return mt_rand(min((int)$min, (int)$max), max((int)$min, (int)$max)) === $value;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@condition.randomNumber.form.min", Language::get("form.example", ["0"]), $default[1] ?? $this->getMin()),
                new Input("@condition.randomNumber.form.max", Language::get("form.example", ["10"]), $default[2] ?? $this->getMax()),
                new Input("@condition.randomNumber.form.value", Language::get("form.example", ["0"]), $default[3] ?? $this->getValue()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        for ($i=1; $i<=3; $i++) {
            if ($data[$i] === "") {
                $errors[] = ["@form.insufficient", $i];
            } elseif (!Main::getVariableHelper()->containsVariable($data[$i]) and !is_numeric($data[$i])) {
                $errors[] = ["@flowItem.error.notNumber", 3];
            }
        }
        return ["status" => empty($errors), "contents" => [$data[1], $data[2]], "cancel" => $data[3], "errors" => $errors];
    }

    public function loadSaveData(array $content): Condition {
        if (!isset($content[2])) throw new \OutOfBoundsException();
        $this->setMin($content[0]);
        $this->setMax($content[1]);
        $this->setValue($content[2]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getMin(), $this->getMax(), $this->getValue()];
    }
}