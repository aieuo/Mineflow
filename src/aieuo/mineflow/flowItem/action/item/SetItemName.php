<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\item;

use aieuo\mineflow\flowItem\argument\ItemArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class SetItemName extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    private ItemArgument $item;
    private StringArgument $itemName;

    public function __construct(string $item = "", string $itemName = "") {
        parent::__construct(self::SET_ITEM_NAME, FlowItemCategory::ITEM);

        $this->setArguments([
            $this->item = new ItemArgument("item", $item),
            $this->itemName = new StringArgument("name", $itemName, "@action.createItem.form.name", example: "aieuo", optional: true),
        ]);
    }

    public function getItem(): ItemArgument {
        return $this->item;
    }

    public function getItemName(): StringArgument {
        return $this->itemName;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $this->itemName->getString($source);
        $item = $this->item->getItem($source);

        $item->setCustomName($name);

        yield Await::ALL;
        return $this->item->get();
    }
}
