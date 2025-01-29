<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\argument\BlockArgument;
use aieuo\mineflow\flowItem\argument\PositionArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\libs\_ac618486ac522f0b\SOFe\AwaitGenerator\Await;

class SetBlock extends SimpleAction {

    public function __construct(string $position = "", string $block = "") {
        parent::__construct(self::SET_BLOCK, FlowItemCategory::WORLD);

        $this->setArguments([
            PositionArgument::create("position", $position),
            BlockArgument::create("block", $block),
        ]);
    }

    public function getPosition(): PositionArgument {
        return $this->getArgument("position");
    }

    public function getBlock(): BlockArgument {
        return $this->getArgument("block");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $position = $this->getPosition()->getPosition($source);
        $block = $this->getBlock()->getBlock($source);

        $position->world->setBlock($position, $block);

        yield Await::ALL;
    }
}