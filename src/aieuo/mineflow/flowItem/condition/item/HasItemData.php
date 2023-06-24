<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\item;

use aieuo\mineflow\flowItem\base\ConditionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\argument\ItemArgument;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use SOFe\AwaitGenerator\Await;

class HasItemData extends FlowItem implements Condition {
    use ConditionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;
    private ItemArgument $item;

    public function __construct(
        string         $item = "",
        private string $key = "",
    ) {
        parent::__construct(self::HAS_ITEM_DATA, FlowItemCategory::ITEM);

        $this->item = new ItemArgument("item", $item);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->item->getName(), "key"];
    }

    public function getDetailReplaces(): array {
        return [$this->item->get(), $this->getKey()];
    }

    public function getItem(): ItemArgument {
        return $this->item;
    }

    public function setKey(string $key): void {
        $this->key = $key;
    }

    public function getKey(): string {
        return $this->key;
    }

    public function isDataValid(): bool {
        return $this->item->isNotEmpty() and $this->getKey() !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $item = $this->item->getItem($source);
        $key = $source->replaceVariables($this->getKey());
        $tags = $item->getNamedTag();

        yield Await::ALL;
        return $tags->getTag($key) !== null;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->item->createFormElement($variables),
            new ExampleInput("@action.setItemData.form.key", "aieuo", $this->getKey(), true),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->item->set($content[0]);
        $this->setKey($content[1]);
    }

    public function serializeContents(): array {
        return [$this->item->get(), $this->getKey()];
    }
}
