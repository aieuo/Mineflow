<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\utils\Language;

abstract class CheckPlayerState extends FlowItem implements Condition, PlayerFlowItem {
    use PlayerFlowItemTrait;

    protected array $detailDefaultReplace = ["player"];

    public function __construct(
        string $id,
        string $category = FlowItemCategory::PLAYER,
        string $player = ""
    ) {
        parent::__construct($id, $category);

        $this->setPlayerVariableName($player);
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "";
    }

    public function getDetail(): string {
        return Language::get($this->detail, [$this->getPlayerVariableName()]);
    }

    public function getEditFormElements(array $variables): array {
        return [
            new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        if (isset($content[0])) $this->setPlayerVariableName($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName()];
    }
}
