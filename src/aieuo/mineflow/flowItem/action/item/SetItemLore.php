<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\item;

use aieuo\mineflow\flowItem\argument\ItemArgument;
use aieuo\mineflow\flowItem\argument\StringArrayArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class SetItemLore extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    private ItemArgument $item;
    private StringArrayArgument $lore;

    public function __construct(string $item = "", string $lore = "") {
        parent::__construct(self::SET_ITEM_LORE, FlowItemCategory::ITEM);

        $this->item = new ItemArgument("item", $item);
        $this->lore = new StringArrayArgument("lore", $lore, separator: ";");
    }

    public function getItem(): ItemArgument {
        return $this->item;
    }

    public function getLore(): StringArrayArgument {
        return $this->lore;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $item = $this->item->getItem($source);
        $lore = $this->lore->getArray($source);

        $item->setLore($lore);

        yield Await::ALL;
        return $this->item->get();
    }
}
