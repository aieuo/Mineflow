<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\argument\ItemArgument;
use aieuo\mineflow\flowItem\argument\PositionArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class DropItem extends SimpleAction {

    private PositionArgument $position;
    private ItemArgument $item;

    public function __construct(string $position = "", string $item = "") {
        parent::__construct(self::DROP_ITEM, FlowItemCategory::WORLD);

        $this->setArguments([
            $this->position = new PositionArgument("position", $position),
            $this->item = new ItemArgument("item", $item),
        ]);
    }

    public function getPosition(): PositionArgument {
        return $this->position;
    }

    public function getItem(): ItemArgument {
        return $this->item;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $position = $this->position->getPosition($source);
        $item = $this->item->getItem($source);

        $position->getWorld()->dropItem($position, $item);

        yield Await::ALL;
    }
}
