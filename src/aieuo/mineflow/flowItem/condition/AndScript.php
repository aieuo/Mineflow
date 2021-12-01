<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemContainer;
use aieuo\mineflow\flowItem\FlowItemContainerTrait;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\ui\FlowItemContainerForm;
use aieuo\mineflow\utils\Category;
use pocketmine\player\Player;

class AndScript extends FlowItem implements Condition, FlowItemContainer {
    use FlowItemContainerTrait;

    protected string $id = self::CONDITION_AND;

    protected string $name = "condition.and.name";
    protected string $detail = "condition.and.detail";

    protected string $category = Category::SCRIPT;

    public function getDetail(): string {
        $details = ["----------and-----------"];
        foreach ($this->getConditions() as $condition) {
            $details[] = $condition->getDetail();
        }
        $details[] = "------------------------";
        return implode("\n", $details);
    }

    public function getContainerName(): string {
        return empty($this->getCustomName()) ? $this->getName() : $this->getCustomName();
    }

    public function execute(FlowItemExecutor $source): \Generator {
        foreach ($this->getConditions() as $condition) {
            if (!(yield from $condition->execute($source))) return false;
        }
        return true;
    }

    public function hasCustomMenu(): bool {
        return true;
    }

    public function getCustomMenuButtons(): array {
        return [
            new Button("@condition.edit", fn(Player $player) => (new FlowItemContainerForm)->sendActionList($player, $this, FlowItemContainer::CONDITION)),
        ];
    }

    public function loadSaveData(array $contents): FlowItem {
        foreach ($contents as $content) {
            $condition = FlowItem::loadEachSaveData($content);
            $this->addItem($condition, FlowItemContainer::CONDITION);
        }
        return $this;
    }

    public function serializeContents(): array {
        return $this->getConditions();
    }

    public function isDataValid(): bool {
        return true;
    }

    public function __clone() {
        $conditions = [];
        foreach ($this->getConditions() as $k => $condition) {
            $conditions[$k] = clone $condition;
        }
        $this->setItems($conditions, FlowItemContainer::CONDITION);
    }
}