<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\condition\block;

use aieuo\mineflow\flowItem\argument\BlockArgument;
use aieuo\mineflow\flowItem\base\SimpleCondition;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\FlowItemIds;
use SOFe\AwaitGenerator\GeneratorUtil;

class IsSameBlock extends SimpleCondition {

    public function __construct(string $block1 = "", string $block2 = "") {
        parent::__construct(FlowItemIds::IS_SAME_BLOCk, FlowItemCategory::BLOCK);

        $this->setArguments([
            new BlockArgument("block1", $block1, "@action.form.target.block (1)"),
            new BlockArgument("block2", $block2, "@action.form.target.block (2)"),
        ]);
    }

    public function getBlock1(): BlockArgument {
        return $this->getArguments()[0];
    }

    public function getBlock2(): BlockArgument {
        return $this->getArguments()[1];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $block1 = $this->getBlock1()->getBlock($source);
        $block2 = $this->getBlock2()->getBlock($source);

        yield from GeneratorUtil::empty();

        return $block1->isSameState($block2);
    }
}
