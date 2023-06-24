<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\inventory;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\placeholder\ItemPlaceholder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use SOFe\AwaitGenerator\Await;

class SetItem extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private ItemPlaceholder $item;

    public function __construct(string $player = "", string $item = "", private string $index = "") {
        parent::__construct(self::SET_ITEM, FlowItemCategory::INVENTORY);

        $this->setPlayerVariableName($player);
        $this->item = new ItemPlaceholder("item", $item);
    }

    public function getDetailDefaultReplaces(): array {
        return ["player", $this->item->getName(), "index"];
    }

    public function getDetailReplaces(): array {
        return [$this->getPlayerVariableName(), $this->item->get(), $this->getIndex()];
    }

    public function getItem(): ItemPlaceholder {
        return $this->item;
    }

    public function setIndex(string $health): void {
        $this->index = $health;
    }

    public function getIndex(): string {
        return $this->index;
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "" and $this->item->isNotEmpty() and $this->index !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $index = $this->getInt($source->replaceVariables($this->getIndex()), 0);
        $player = $this->getOnlinePlayer($source);

        $item = $this->item->getItem($source);

        $player->getInventory()->setItem($index, $item);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
            $this->item->createFormElement($variables),
            new ExampleNumberInput("@action.setItem.form.index", "0", $this->getIndex(), true, 0),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->setPlayerVariableName($content[0]);
        $this->item->set($content[1]);
        $this->setIndex($content[2]);
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->item->get(), $this->getIndex()];
    }
}
