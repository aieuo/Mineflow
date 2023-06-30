<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\argument\BlockArgument;
use aieuo\mineflow\flowItem\argument\PositionArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class SetBlock extends SimpleAction {

    private BlockArgument $block;
    private PositionArgument $position;

    public function __construct(string $position = "", string $block = "") {
        parent::__construct(self::SET_BLOCK, FlowItemCategory::WORLD);

        $this->setArguments([
            $this->position = new PositionArgument("position", $position),
            $this->block = new BlockArgument("block", $block),
        ]);
    }

    public function getPosition(): PositionArgument {
        return $this->position;
    }

    public function getBlock(): BlockArgument {
        return $this->block;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $position = $this->position->getPosition($source);
        $block = $this->block->getBlock($source);

        $position->world->setBlock($position, $block);

        yield Await::ALL;
    }
}
