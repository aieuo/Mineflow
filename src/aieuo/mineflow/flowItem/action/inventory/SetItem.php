<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\inventory;

use aieuo\mineflow\flowItem\base\ItemFlowItem;
use aieuo\mineflow\flowItem\base\ItemFlowItemTrait;
use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\formAPI\element\mineflow\ItemVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\utils\Language;

class SetItem extends FlowItem implements PlayerFlowItem, ItemFlowItem {
    use PlayerFlowItemTrait, ItemFlowItemTrait;

    protected string $name = "action.setItem.name";
    protected string $detail = "action.setItem.detail";
    protected array $detailDefaultReplace = ["player", "item", "index"];

    public function __construct(string $player = "", string $item = "", private string $index = "") {
        parent::__construct(self::SET_ITEM, FlowItemCategory::INVENTORY);

        $this->setPlayerVariableName($player);
        $this->setItemVariableName($item);
    }

    public function setIndex(string $health): void {
        $this->index = $health;
    }

    public function getIndex(): string {
        return $this->index;
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "" and $this->getItemVariableName() !== "" and $this->index !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPlayerVariableName(), $this->getItemVariableName(), $this->getIndex()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $index = $source->replaceVariables($this->getIndex());

        $this->throwIfInvalidNumber($index, 0);

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        $item = $this->getItem($source);

        $player->getInventory()->setItem((int)$index, $item);
        yield true;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
            new ItemVariableDropdown($variables, $this->getItemVariableName()),
            new ExampleNumberInput("@action.setItem.form.index", "0", $this->getIndex(), true, 0),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setPlayerVariableName($content[0]);
        $this->setItemVariableName($content[1]);
        $this->setIndex($content[2]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->getItemVariableName(), $this->getIndex()];
    }
}