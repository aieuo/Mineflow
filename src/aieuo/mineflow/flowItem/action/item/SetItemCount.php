<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\item;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\argument\ItemArgument;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use SOFe\AwaitGenerator\Await;

class SetItemCount extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;
    private ItemArgument $item;

    public function __construct(string $item = "", private string $count = "") {
        parent::__construct(self::SET_ITEM_COUNT, FlowItemCategory::ITEM);

        $this->item = new ItemArgument("item", $item);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->item->getName(), "count"];
    }

    public function getDetailReplaces(): array {
        return [$this->item->get(), $this->getCount()];
    }

    public function getItem(): ItemArgument {
        return $this->item;
    }

    public function setCount(string $count): void {
        $this->count = $count;
    }

    public function getCount(): string {
        return $this->count;
    }

    public function isDataValid(): bool {
        return $this->item->isNotEmpty() and $this->count !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $count = $this->getInt($source->replaceVariables($this->getCount()), 0);
        $item = $this->item->getItem($source);

        $item->setCount($count);

        yield Await::ALL;
        return $this->item->get();
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->item->createFormElement($variables),
            new ExampleNumberInput("@action.createItem.form.count", "64", $this->getCount(), true, 0),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->item->set($content[0]);
        $this->setCount($content[1]);
    }

    public function serializeContents(): array {
        return [$this->item->get(), $this->getCount()];
    }
}
