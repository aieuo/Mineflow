<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\inventory;

use aieuo\mineflow\flowItem\base\ItemFlowItem;
use aieuo\mineflow\flowItem\base\ItemFlowItemTrait;
use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\formAPI\element\mineflow\ItemVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;

class SetItem extends FlowItem implements PlayerFlowItem, ItemFlowItem {
    use PlayerFlowItemTrait, ItemFlowItemTrait;

    protected string $id = self::SET_ITEM;

    protected string $name = "action.setItem.name";
    protected string $detail = "action.setItem.detail";
    protected array $detailDefaultReplace = ["player", "item", "index"];

    protected string $category = Category::INVENTORY;

    private string $index;

    public function __construct(string $player = "", string $item = "", string $index = "") {
        $this->setPlayerVariableName($player);
        $this->setItemVariableName($item);
        $this->index = $index;
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
        return Language::get($this->detail, [$this->getPlayerVariableName(), $this->getItemVariableName(), $this->getIndex()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $index = $this->getInt($source->replaceVariables($this->getIndex()), 0);
        $player = $this->getOnlinePlayer($source);

        $item = $this->getItem($source);

        $player->getInventory()->setItem($index, $item);
        yield FlowItemExecutor::CONTINUE;
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