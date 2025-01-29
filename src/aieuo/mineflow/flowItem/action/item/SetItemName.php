<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\item;

use aieuo\mineflow\flowItem\argument\ItemArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\libs\_ddbd776e705b8daa\SOFe\AwaitGenerator\Await;

class SetItemName extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    public function __construct(string $item = "", string $itemName = "") {
        parent::__construct(self::SET_ITEM_NAME, FlowItemCategory::ITEM);

        $this->setArguments([
            ItemArgument::create("item", $item),
            StringArgument::create("name", $itemName, "@action.createItem.form.name")->optional()->example("aieuo"),
        ]);
    }

    public function getItem(): ItemArgument {
        return $this->getArgument("item");
    }

    public function getItemName(): StringArgument {
        return $this->getArgument("name");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $this->getItemName()->getString($source);
        $item = $this->getItem()->getItem($source);

        $item->setCustomName($name);

        yield Await::ALL;
        return (string)$this->getItem();
    }
}