<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\item;

use aieuo\mineflow\flowItem\argument\ItemArgument;
use aieuo\mineflow\flowItem\argument\StringArrayArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\libs\_6c37ba9df39eb43f\SOFe\AwaitGenerator\Await;

class SetItemLore extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    public function __construct(string $item = "", string $lore = "") {
        parent::__construct(self::SET_ITEM_LORE, FlowItemCategory::ITEM);

        $this->setArguments([
            ItemArgument::create("item", $item),
            StringArrayArgument::create("lore", $lore)->separator(";")->optional(),
        ]);
    }

    public function getItem(): ItemArgument {
        return $this->getArgument("item");
    }

    public function getLore(): StringArrayArgument {
        return $this->getArgument("lore");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $item = $this->getItem()->getItem($source);
        $lore = $this->getLore()->getArray($source);

        $item->setLore($lore);

        yield Await::ALL;
        return (string)$this->getItem();
    }
}