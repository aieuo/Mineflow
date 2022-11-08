<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemContainer;
use aieuo\mineflow\flowItem\FlowItemContainerTrait;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\ui\FlowItemContainerForm;
use pocketmine\player\Player;

abstract class IFActionBase extends FlowItem implements FlowItemContainer {
    use FlowItemContainerTrait;
    use ActionNameWithMineflowLanguage;

    public function __construct(
        string  $id,
        string  $category = FlowItemCategory::SCRIPT_IF,
        array   $conditions = [],
        array   $actions = [],
        ?string $customName = null
    ) {
        parent::__construct($id, $category);

        $this->setItems($conditions, FlowItemContainer::CONDITION);
        $this->setItems($actions, FlowItemContainer::ACTION);
        $this->setCustomName($customName);
    }

    public function getDetail(): string {
        $details = ["", "§7=============§f ".$this->getId()." §7===============§f"];
        foreach ($this->getConditions() as $condition) {
            $details[] = $condition->getDetail();
        }
        $details[] = "§7~~~~~~~~~~~~~~~~~~~~~~~~~~~§f";
        foreach ($this->getActions() as $action) {
            $details[] = $action->getDetail();
        }
        $details[] = "§7================================§f";
        return implode("\n", $details);
    }

    public function getContainerName(): string {
        return empty($this->getCustomName()) ? $this->getName() : $this->getCustomName();
    }

    public function isDataValid(): bool {
        return true;
    }

    public function hasCustomMenu(): bool {
        return true;
    }

    public function getCustomMenuButtons(): array {
        return [
            new Button("@condition.edit", fn(Player $player) => (new FlowItemContainerForm)->sendActionList($player, $this, FlowItemContainer::CONDITION)),
            new Button("@action.edit", fn(Player $player) => (new FlowItemContainerForm)->sendActionList($player, $this, FlowItemContainer::ACTION)),
        ];
    }

    public function loadSaveData(array $contents): FlowItem {
        foreach ($contents[0] as $content) {
            $condition = FlowItem::loadEachSaveData($content);
            $this->addItem($condition, FlowItemContainer::CONDITION);
        }

        foreach ($contents[1] as $content) {
            $action = FlowItem::loadEachSaveData($content);
            $this->addItem($action, FlowItemContainer::ACTION);
        }
        return $this;
    }

    public function serializeContents(): array {
        return [
            $this->getConditions(),
            $this->getActions()
        ];
    }

    public function allowDirectCall(): bool {
        return false;
    }

    public function __clone() {
        $conditions = [];
        foreach ($this->getConditions() as $k => $condition) {
            $conditions[$k] = clone $condition;
        }
        $this->setItems($conditions, FlowItemContainer::CONDITION);

        $actions = [];
        foreach ($this->getActions() as $k => $action) {
            $actions[$k] = clone $action;
        }
        $this->setItems($actions, FlowItemContainer::ACTION);
    }
}
