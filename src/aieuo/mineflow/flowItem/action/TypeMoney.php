<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Toggle;

abstract class TypeMoney extends Action {

    protected $detailDefaultReplace = ["target", "amount"];

    protected $category = Category::PLUGIN;

    /** @var string */
    private $playerName = "{target.name}";
    /** @var string */
    private $amount;

    public function __construct(string $name = "{target.name}", int $amount = null) {
        $this->playerName = $name;
        $this->amount = (string)$amount;
    }

    public function setPlayerName(string $name): self {
        $this->playerName = $name;
        return $this;
    }

    public function getPlayerName(): string {
        return $this->playerName;
    }

    public function setAmount(string $amount): self {
        $this->amount = $amount;
        return $this;
    }

    public function getAmount(): string {
        return $this->amount;
    }

    public function isDataValid(): bool {
        return $this->getPlayerName() !== "" and $this->getAmount() !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPlayerName(), $this->getAmount()]);
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@action.money.form.target", Language::get("form.example", ["{target.name}"]), $default[1] ?? $this->getPlayerName()),
                new Input("@action.money.form.amount", Language::get("form.example", ["1000"]), $default[2] ?? $this->getAmount()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[1] === "") $data[1] = "{target.name}";
        if ($data[2] === "") {
            $errors = [["@form.insufficient", 2]];
        }
        return ["status" => empty($errors), "contents" => [$data[1], $data[2]], "cancel" => $data[3], "errors" => $errors];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[1])) throw new \OutOfBoundsException();

        $this->setPlayerName($content[0]);
        $this->setAmount($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerName(), $this->getAmount()];
    }
}