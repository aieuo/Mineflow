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

    private BlockArgument $block1;
    private BlockArgument $block2;

    public function __construct(string $block1 = "", string $block2 = "") {
        parent::__construct(FlowItemIds::IS_SAME_BLOCk, FlowItemCategory::BLOCK);

        $this->setArguments([
            $this->block1 = new BlockArgument("block1", $block1, "@action.form.target.block (1)"),
            $this->block2 = new BlockArgument("block2", $block2, "@action.form.target.block (2)"),
        ]);
    }

    public function getBlock1(): BlockArgument {
        return $this->block1;
    }

    public function getBlock2(): BlockArgument {
        return $this->block2;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $block1 = $this->block1->getBlock($source);
        $block2 = $this->block2->getBlock($source);

        yield from GeneratorUtil::empty();

        return $block1->isSameState($block2);
    }
}
