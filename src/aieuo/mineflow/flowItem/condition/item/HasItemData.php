<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\item;

use aieuo\mineflow\flowItem\base\ConditionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\ItemFlowItem;
use aieuo\mineflow\flowItem\base\ItemFlowItemTrait;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\ItemVariableDropdown;
use SOFe\AwaitGenerator\Await;

class HasItemData extends FlowItem implements Condition, ItemFlowItem {
    use ItemFlowItemTrait;
    use ConditionNameWithMineflowLanguage;

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    public function __construct(
        string         $item = "",
        private string $key = "",
    ) {
        parent::__construct(self::HAS_ITEM_DATA, FlowItemCategory::ITEM);

        $this->setItemVariableName($item);
    }

    public function getDetailDefaultReplaces(): array {
        return ["item", "key"];
    }

    public function getDetailReplaces(): array {
        return [$this->getItemVariableName(), $this->getKey()];
    }

    public function setKey(string $key): void {
        $this->key = $key;
    }

    public function getKey(): string {
        return $this->key;
    }

    public function isDataValid(): bool {
        return $this->getItemVariableName() !== "" and $this->getKey() !== "";
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $item = $this->getItem($source);
        $key = $source->replaceVariables($this->getKey());
        $tags = $item->getNamedTag();

        yield Await::ALL;
        return $tags->getTag($key) !== null;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ItemVariableDropdown($variables, $this->getItemVariableName()),
            new ExampleInput("@action.setItemData.form.key", "aieuo", $this->getKey(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setItemVariableName($content[0]);
        $this->setKey($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getItemVariableName(), $this->getKey()];
    }
}
