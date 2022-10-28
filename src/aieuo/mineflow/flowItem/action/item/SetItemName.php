<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\item;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\ItemFlowItem;
use aieuo\mineflow\flowItem\base\ItemFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\ItemVariableDropdown;
use aieuo\mineflow\utils\Language;
use SOFe\AwaitGenerator\Await;

class SetItemName extends FlowItem implements ItemFlowItem {
    use ItemFlowItemTrait;
    use ActionNameWithMineflowLanguage;

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    public function __construct(string $item = "", private string $itemName = "") {
        parent::__construct(self::SET_ITEM_NAME, FlowItemCategory::ITEM);

        $this->setItemVariableName($item);
    }

    public function getDetailDefaultReplaces(): array {
        return ["item", "name"];
    }

    public function getDetailReplaces(): array {
        return [$this->getItemVariableName(), $this->getItemName()];
    }

    public function setItemName(string $itemName): void {
        $this->itemName = $itemName;
    }

    public function getItemName(): string {
        return $this->itemName;
    }

    public function isDataValid(): bool {
        return $this->getItemVariableName() !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $source->replaceVariables($this->getItemName());

        $item = $this->getItem($source);

        $item->setCustomName($name);

        yield Await::ALL;
        return $this->getItemVariableName();
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ItemVariableDropdown($variables, $this->getItemVariableName()),
            new ExampleInput("@action.createItem.form.name", "aieuo", $this->getItemName(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setItemVariableName($content[0]);
        $this->setItemName($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getItemVariableName(), $this->getItemName()];
    }
}
