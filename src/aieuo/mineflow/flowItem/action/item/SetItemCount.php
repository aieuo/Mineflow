<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\item;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\ItemFlowItem;
use aieuo\mineflow\flowItem\base\ItemFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\formAPI\element\mineflow\ItemVariableDropdown;
use aieuo\mineflow\utils\Language;
use SOFe\AwaitGenerator\Await;

class SetItemCount extends FlowItem implements ItemFlowItem {
    use ItemFlowItemTrait;
    use ActionNameWithMineflowLanguage;

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    public function __construct(string $item = "", private string $count = "") {
        parent::__construct(self::SET_ITEM_COUNT, FlowItemCategory::ITEM);

        $this->setItemVariableName($item);
    }

    public function getDetailDefaultReplaces(): array {
        return ["item", "count"];
    }

    public function getDetailReplaces(): array {
        return [$this->getItemVariableName(), $this->getCount()];
    }

    public function setCount(string $count): void {
        $this->count = $count;
    }

    public function getCount(): string {
        return $this->count;
    }

    public function isDataValid(): bool {
        return $this->getItemVariableName() !== "" and $this->count !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $count = $this->getInt($source->replaceVariables($this->getCount()), 0);
        $item = $this->getItem($source);

        $item->setCount($count);

        yield Await::ALL;
        return $this->getItemVariableName();
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ItemVariableDropdown($variables, $this->getItemVariableName()),
            new ExampleNumberInput("@action.createItem.form.count", "64", $this->getCount(), true, 0),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setItemVariableName($content[0]);
        $this->setCount($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getItemVariableName(), $this->getCount()];
    }
}
