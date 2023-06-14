<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\condition\block;

use aieuo\mineflow\flowItem\base\BlockFlowItem;
use aieuo\mineflow\flowItem\base\BlockFlowItemTrait;
use aieuo\mineflow\flowItem\base\ConditionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\FlowItemIds;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\BlockVariableDropdown;
use SOFe\AwaitGenerator\GeneratorUtil;

class IsSameBlock extends FlowItem implements Condition, BlockFlowItem {
    use BlockFlowItemTrait;
    use ConditionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private const KEY_BLOCK1 = "block1";
    private const KEY_BLOCK2 = "block2";

    public function __construct(string $block1 = "", string $block2 = "") {
        parent::__construct(FlowItemIds::IS_SAME_BLOCk, FlowItemCategory::BLOCK);

        $this->setBlockVariableName($block1, self::KEY_BLOCK1);
        $this->setBlockVariableName($block2, self::KEY_BLOCK2);
    }

    public function getDetailDefaultReplaces(): array {
        return ["block1", "block2"];
    }

    public function getDetailReplaces(): array {
        return [$this->getBlockVariableName(self::KEY_BLOCK1), $this->getBlockVariableName(self::KEY_BLOCK2)];
    }

    public function isDataValid(): bool {
        return $this->getBlockVariableName(self::KEY_BLOCK1) !== "" and $this->getBlockVariableName(self::KEY_BLOCK2) !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $block1 = $this->getBlock($source, self::KEY_BLOCK1);
        $block2 = $this->getBlock($source, self::KEY_BLOCK2);

        yield from GeneratorUtil::empty();

        return $block1->isSameState($block2);
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new BlockVariableDropdown($variables, $this->getBlockVariableName(self::KEY_BLOCK1), "@action.form.target.block (1)"),
            new BlockVariableDropdown($variables, $this->getBlockVariableName(self::KEY_BLOCK2), "@action.form.target.block (2)"),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->setBlockVariableName($content[0], self::KEY_BLOCK1);
        $this->setBlockVariableName($content[1], self::KEY_BLOCK2);
    }

    public function serializeContents(): array {
        return [$this->getBlockVariableName(self::KEY_BLOCK1), $this->getBlockVariableName(self::KEY_BLOCK2)];
    }
}