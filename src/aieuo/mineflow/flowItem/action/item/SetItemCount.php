<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\item;

use aieuo\mineflow\flowItem\argument\ItemArgument;
use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class SetItemCount extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    private ItemArgument $item;
    private NumberArgument $count;

    public function __construct(string $item = "", int $count = null) {
        parent::__construct(self::SET_ITEM_COUNT, FlowItemCategory::ITEM);

        $this->setArguments([
            $this->item = new ItemArgument("item", $item),
            $this->count = new NumberArgument("count", $count, "@action.createItem.form.count", example: "64", min: 0),
        ]);
    }

    public function getItem(): ItemArgument {
        return $this->item;
    }

    public function getCount(): NumberArgument {
        return $this->count;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $count = $this->count->getInt($source);
        $item = $this->item->getItem($source);

        $item->setCount($count);

        yield Await::ALL;
        return $this->item->get();
    }
}
