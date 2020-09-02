<?php

namespace aieuo\mineflow\flowItem\condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\ExampleInput;
use aieuo\mineflow\formAPI\element\ExampleNumberInput;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\CustomForm;

abstract class TypeMoney extends FlowItem implements Condition {

    protected $detailDefaultReplace = ["target", "amount"];

    protected $category = Category::PLUGIN;

    /** @var string */
    private $playerName;
    /** @var string */
    private $amount;

    public function __construct(string $name = "{target.name}", string $amount = null) {
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

    public function getAmount(): ?string {
        return $this->amount;
    }

    public function isDataValid(): bool {
        return $this->getPlayerName() !== "" and $this->amount !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPlayerName(), $this->getAmount()]);
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@action.money.form.target", "{target.name}", $default[1] ?? $this->getPlayerName(), true),
                new ExampleNumberInput("@action.money.form.amount", "1000", $default[2] ?? $this->getAmount(), true),
                new CancelToggle()
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], $data[2]], "cancel" => $data[3], "errors" => []];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setPlayerName($content[0]);
        $this->setAmount($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerName(), $this->getAmount()];
    }
}