<?php

namespace aieuo\mineflow\condition;

use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\Main;

abstract class TypeMoney extends Condition {

    protected $category = Categories::CATEGORY_CONDITION_MONEY;

    /** @var string */
    private $amount;

    public function __construct(int $amount = null) {
        $this->amount = (string)$amount;
    }

    public function setAmount(string $amount): self {
        $this->amount = $amount;
        return $this;
    }

    public function getAmount(): ?string {
        return $this->amount;
    }

    public function isDataValid(): bool {
        return $this->amount !== null;
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getAmount()]);
    }

    public function getEditForm(array $default = [], array $errors = []) {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@condition.money.form.amount", Language::get("form.example", ["100"]), $default[1] ?? $this->getAmount()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $status = true;
        $errors = [];
        $containsVariable = Main::getInstance()->getVariableHelper()->containsVariable($data[1]);
        if ($data[1] === "") {
            $status = false;
            $errors = [["@form.insufficient", 1]];
        } elseif (!$containsVariable and !is_numeric($data[1])) {
            $status = false;
            $errors = [["@mineflow.contents.notNumber", 1]];
        } elseif (!$containsVariable and (int)$data[1] <= 0) {
            $status = false;
            $errors = [["@condition.money.zero", 1]];
        }
        return ["status" => $status, "contents" => [$data[1]], "cancel" => $data[2], "errors" => $errors];
    }

    public function parseFromSaveData(array $content): ?Condition {
        if (!isset($content[0])) return null;

        $this->setAmount($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getAmount()];
    }
}