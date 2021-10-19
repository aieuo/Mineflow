<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;

abstract class TypeMoney extends FlowItem implements Condition {

    protected array $detailDefaultReplace = ["target", "amount"];

    protected string $category = Category::PLUGIN;

    private string $playerName;
    private string $amount;

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
        return Language::get($this->detail, [$this->getPlayerName(), $this->getAmount()]);
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.money.form.target", "{target.name}", $this->getPlayerName(), true),
            new ExampleNumberInput("@action.money.form.amount", "1000", $this->getAmount(), true),
        ];
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