<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\condition\item;

use aieuo\mineflow\flowItem\argument\BooleanArgument;
use aieuo\mineflow\flowItem\argument\ItemArgument;
use aieuo\mineflow\flowItem\base\SimpleCondition;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\FlowItemIds;
use aieuo\mineflow\utils\Language;
use SOFe\AwaitGenerator\GeneratorUtil;

class IsSameItem extends SimpleCondition {

    private ItemArgument $item1;
    private ItemArgument $item2;
    private BooleanArgument $checkCompound;

    public function __construct(string $item1 = "", string $item2 = "", bool $checkCompound = false) {
        parent::__construct(FlowItemIds::IS_SAME_ITEM, FlowItemCategory::ITEM);

        $this->setArguments([
            $this->item1 = new ItemArgument("item1", $item1, "@action.form.target.item (1)"),
            $this->item2 = new ItemArgument("item2", $item2, "@action.form.target.item (2)"),
            $this->checkCompound = new BooleanArgument("tag", $checkCompound, "@condition.isSameItem.form.checkCompound"),
        ]);
    }

    public function getDetailReplaces(): array {
        return [
            $this->item1->get(),
            $this->item2->get(),
            Language::get($this->checkCompound->getBool() ? "form.yes" : "form.no"),
        ];
    }

    public function getItem1(): ItemArgument {
        return $this->item1;
    }

    public function getItem2(): ItemArgument {
        return $this->item2;
    }

    public function getCheckCompound(): BooleanArgument {
        return $this->checkCompound;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $item1 = $this->item1->getItem($source);
        $item2 = $this->item2->getItem($source);

        yield from GeneratorUtil::empty();
        return $item1->equals($item2, checkCompound: $this->checkCompound->getBool());
    }
}
