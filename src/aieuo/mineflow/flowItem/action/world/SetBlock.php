<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\PositionFlowItem;
use aieuo\mineflow\flowItem\base\PositionFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\placeholder\BlockPlaceholder;
use aieuo\mineflow\formAPI\element\mineflow\PositionVariableDropdown;
use SOFe\AwaitGenerator\Await;

class SetBlock extends FlowItem implements PositionFlowItem {
    use PositionFlowItemTrait;
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private BlockPlaceholder $block;

    public function __construct(string $position = "", string $block = "") {
        parent::__construct(self::SET_BLOCK, FlowItemCategory::WORLD);

        $this->setPositionVariableName($position);
        $this->block = new BlockPlaceholder("block", $block);
    }

    public function getDetailDefaultReplaces(): array {
        return ["position", $this->block->getName()];
    }

    public function getDetailReplaces(): array {
        return [$this->getPositionVariableName(), $this->block->get()];
    }

    public function getBlock(): BlockPlaceholder {
        return $this->block;
    }

    public function isDataValid(): bool {
        return $this->getPositionVariableName() !== "" and $this->block->isNotEmpty();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $position = $this->getPosition($source);

        $block = $this->block->getBlock($source);

        $position->world->setBlock($position, $block);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new PositionVariableDropdown($variables, $this->getPositionVariableName()),
            $this->block->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->setPositionVariableName($content[0]);
        $this->block->set($content[1]);
    }

    public function serializeContents(): array {
        return [$this->getPositionVariableName(), $this->block->get()];
    }
}
