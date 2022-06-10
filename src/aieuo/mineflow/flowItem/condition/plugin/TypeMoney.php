<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\plugin;

use aieuo\mineflow\flowItem\base\ConditionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;

abstract class TypeMoney extends FlowItem implements Condition {
    use ConditionNameWithMineflowLanguage;

    public function __construct(
        string         $id,
        string         $category = FlowItemCategory::PLUGIN,
        private string $playerName = "{target.name}",
        private string $amount = "",
    ) {
        parent::__construct($id, $category);
    }

    public function getDetailDefaultReplaces(): array {
        return ["target", "amount"];
    }

    public function getDetailReplaces(): array {
        return [$this->getPlayerName(), $this->getAmount()];
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
