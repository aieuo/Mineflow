<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script\ifelse;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemContainer;
use aieuo\mineflow\flowItem\FlowItemContainerTrait;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\ui\FlowItemContainerForm;
use pocketmine\player\Player;

class ElseAction extends FlowItem implements FlowItemContainer {
    use FlowItemContainerTrait;
    use ActionNameWithMineflowLanguage;

    public function __construct(array $actions = [], ?string $customName = null) {
        parent::__construct(self::ACTION_ELSE, FlowItemCategory::SCRIPT_IF);

        $this->setActions($actions);
        $this->setCustomName($customName);
    }

    public function getDetail(): string {
        $details = ["§7=============§f else §7=============§f"];
        foreach ($this->getActions() as $action) {
            $details[] = $action->getDetail();
        }
        $details[] = "§7================================§f";
        return implode("\n", $details);
    }

    public function getContainerName(): string {
        return empty($this->getCustomName()) ? $this->getName() : $this->getCustomName();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $lastResult = $source->getLastResult();
        if (!is_bool($lastResult)) throw new InvalidFlowValueException($this->getName());
        if ($lastResult) return;

        yield from (new FlowItemExecutor($this->getActions(), $source->getTarget(), [], $source))->getGenerator();
    }

    public function hasCustomMenu(): bool {
        return true;
    }

    public function getCustomMenuButtons(): array {
        return [
            new Button("@action.edit", fn(Player $player) => (new FlowItemContainerForm)->sendActionList($player, $this, FlowItemContainer::ACTION)),
        ];
    }

    public function loadSaveData(array $contents): FlowItem {
        foreach ($contents as $content) {
            $action = FlowItem::loadEachSaveData($content);
            $this->addAction($action);
        }
        return $this;
    }

    public function serializeContents(): array {
        return $this->getActions();
    }

    public function isDataValid(): bool {
        return true;
    }

    public function allowDirectCall(): bool {
        return false;
    }

    public function __clone() {
        $actions = [];
        foreach ($this->getActions() as $k => $action) {
            $actions[$k] = clone $action;
        }
        $this->setActions($actions);
    }
}
