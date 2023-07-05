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

    public function __construct(string $item = "", string $lore = "") {
        parent::__construct(self::SET_ITEM_LORE, FlowItemCategory::ITEM);

        $this->setArguments([
            new ItemArgument("item", $item),
            new StringArrayArgument("lore", $lore, optional: true, separator: ";"),
        ]);
    }

    public function getItem(): ItemArgument {
        return $this->getArguments()[0];
    }

    public function getLore(): StringArrayArgument {
        return $this->getArguments()[1];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $item = $this->getItem()->getItem($source);
        $lore = $this->getLore()->getArray($source);

        $item->setLore($lore);

        yield Await::ALL;
        return (string)$this->getItem();
    }
}
