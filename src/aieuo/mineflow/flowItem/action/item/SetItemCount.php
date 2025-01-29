<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\item;

use aieuo\mineflow\flowItem\argument\ItemArgument;
use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\libs\_ddbd776e705b8daa\SOFe\AwaitGenerator\Await;

class SetItemCount extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    public function __construct(string $item = "", int $count = null) {
        parent::__construct(self::SET_ITEM_COUNT, FlowItemCategory::ITEM);

        $this->setArguments([
            ItemArgument::create("item", $item),
            NumberArgument::create("count", $count, "@action.createItem.form.count")->min(0)->example("64"),
        ]);
    }

    public function getItem(): ItemArgument {
        return $this->getArgument("item");
    }

    public function getCount(): NumberArgument {
        return $this->getArgument("count");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $count = $this->getCount()->getInt($source);
        $item = $this->getItem()->getItem($source);

        $item->setCount($count);

        yield Await::ALL;
        return (string)$this->getItem();
    }
}