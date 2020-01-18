<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Toggle;

abstract class TypeMoney extends Action {

    protected $detailDefaultReplace = ["amount"];

    protected $category = Categories::CATEGORY_ACTION_MONEY;

    /** @var string */
    private $amount;

    public function __construct(int $amount = null) {
        $this->amount = (string)$amount;
    }

    public function setAmount(string $amount): self {
        $this->amount = $amount;
        return $this;
    }

    public function getAmount(): string {
        return $this->amount;
    }

    public function isDataValid(): bool {
        return !empty($this->getAmount());
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getAmount()]);
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@action.money.form.amount", Language::get("form.example", ["1000"]), $default[1] ?? $this->getAmount()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[1] === "") {
            $errors = [["@form.insufficient", 1]];
        }
        return ["status" => empty($errors), "contents" => [$data[1]], "cancel" => $data[2], "errors" => $errors];
    }

    public function loadSaveData(array $content): ?Action {
        if (empty($content[0])) return null;

        $this->setAmount($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getAmount()];
    }
}