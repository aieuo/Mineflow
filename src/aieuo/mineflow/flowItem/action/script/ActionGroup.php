<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemContainer;
use aieuo\mineflow\flowItem\FlowItemContainerTrait;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\ui\FlowItemContainerForm;
use aieuo\mineflow\utils\Language;
use pocketmine\player\Player;

class ActionGroup extends FlowItem implements FlowItemContainer {
    use FlowItemContainerTrait;

    public function __construct(array $actions = [], ?string $customName = null) {
        parent::__construct(self::ACTION_GROUP, FlowItemCategory::SCRIPT);

        $this->setActions($actions);
        $this->setCustomName($customName);
    }

    public function getName(): string {
        return Language::get("action.group.name");
    }

    public function getDescription(): string {
        return Language::get("action.group.description");
    }

    public function getDetail(): string {
        $details = ["", "§7------------------------§f"];
        foreach ($this->getActions() as $action) {
            $details[] = $action->getShortDetail();
        }
        $details[] = "§7------------------------§f\n";
        return implode("\n", $details);
    }

    public function getContainerName(): string {
        return empty($this->getCustomName()) ? $this->getName() : $this->getCustomName();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        yield from (new FlowItemExecutor($this->getActions(), $source->getTarget(), [], $source))->getGenerator();
    }

    public function isDataValid(): bool {
        return true;
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
