<?php

namespace aieuo\mineflow\action\process;

use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\action\process\Process;
use aieuo\mineflow\FormAPI\element\Toggle;

abstract class TypeMoney extends Process {

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

    public function getEditForm(array $default = [], array $errors = []) {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@action.money.form.amount", Language::get("form.example", ["1000"]), $default[1] ?? $this->getAmount()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $status = true;
        $errors = [];
        if ($data[1] === "") {
            $status = false;
            $errors = [["@form.insufficient", 1]];
        }
        return ["status" => $status, "contents" => [$data[1]], "cancel" => $data[2], "errors" => $errors];
    }

    public function parseFromSaveData(array $content): ?Process {
        if (empty($content[0])) return null;

        $this->setAmount($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getAmount()];
    }
}