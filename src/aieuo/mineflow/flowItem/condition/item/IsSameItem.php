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
use aieuo\mineflow\libs\_ddbd776e705b8daa\SOFe\AwaitGenerator\GeneratorUtil;

class IsSameItem extends SimpleCondition {

    public function __construct(string $item1 = "", string $item2 = "", bool $checkCompound = false) {
        parent::__construct(FlowItemIds::IS_SAME_ITEM, FlowItemCategory::ITEM);

        $this->setArguments([
            ItemArgument::create("item1", $item1, "@action.form.target.item (1)"),
            ItemArgument::create("item2", $item2, "@action.form.target.item (2)"),
            BooleanArgument::create("tag", $checkCompound, "@condition.isSameItem.form.checkCompound")
                ->format(fn(bool $value) => Language::get($value ? "form.yes" : "form.no")),
        ]);
    }

    public function getItem1(): ItemArgument {
        return $this->getArgument("item1");
    }

    public function getItem2(): ItemArgument {
        return $this->getArgument("item2");
    }

    public function getCheckCompound(): BooleanArgument {
        return $this->getArgument("tag");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $item1 = $this->getItem1()->getItem($source);
        $item2 = $this->getItem2()->getItem($source);

        yield from GeneratorUtil::empty();
        return $item1->equals($item2, checkCompound: $this->getCheckCompound()->getBool());
    }
}