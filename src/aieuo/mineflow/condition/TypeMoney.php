<?php

namespace aieuo\mineflow\condition;

use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\condition\Condition;
use aieuo\mineflow\FormAPI\element\Toggle;

abstract class TypeMoney extends Condition {

    protected $category = Categories::CATEGORY_CONDITION_MONEY;

    /** @var int */
    private $amount;

    public function __construct(int $amount = null) {
        $this->amount = $amount;
    }

    public function setAmount(int $amount): self {
        $this->amount = $amount;
        return $this;
    }

    public function getAmount(): ?int {
        return $this->amount;
    }

    public function isDataValid(): bool {
        return $this->amount !== null and $this->amount > 0;
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getAmount()]);
    }

    public function getEditForm(array $default = [], array $errors = []) {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@condition.money.form.amount", Language::get("form.example", ["100"]), $default[1] ?? (string)$this->getAmount()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $status = true;
        $errors = [];
        if ($data[1] === "") {
            $status = false;
            $errors = [["@form.insufficient", 1]];
        } elseif (!is_numeric($data[1])) {
            $status = false;
            $errors = [["@condition.money.notNumber", 1]];
        } elseif ((int)$data[1] <= 0) {
            $status = false;
            $errors = [["@condition.money.zero", 1]];
        }
        return ["status" => $status, "contents" => [(int)$data[1]], "cancel" => $data[2], "errors" => $errors];
    }

    public function parseFromSaveData(array $content): ?Condition {
        if (!isset($content[0]) or !is_int($content[0])) return null;

        $this->setAmount($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getAmount()];
    }
}