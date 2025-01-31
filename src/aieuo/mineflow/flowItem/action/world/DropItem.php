<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\argument\ItemArgument;
use aieuo\mineflow\flowItem\argument\PositionArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\libs\_6b4cfdc0a11de6c9\SOFe\AwaitGenerator\Await;

class DropItem extends SimpleAction {

    public function __construct(string $position = "", string $item = "") {
        parent::__construct(self::DROP_ITEM, FlowItemCategory::WORLD);

        $this->setArguments([
            PositionArgument::create("position", $position),
            ItemArgument::create("item", $item),
        ]);
    }

    public function getPosition(): PositionArgument {
        return $this->getArgument("position");
    }

    public function getItem(): ItemArgument {
        return $this->getArgument("item");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $position = $this->getPosition()->getPosition($source);
        $item = $this->getItem()->getItem($source);

        $position->getWorld()->dropItem($position, $item);

        yield Await::ALL;
    }
}