<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\argument\BlockArgument;
use aieuo\mineflow\flowItem\argument\PositionArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use SOFe\AwaitGenerator\Await;

class SetBlock extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private BlockArgument $block;
    private PositionArgument $position;

    public function __construct(string $position = "", string $block = "") {
        parent::__construct(self::SET_BLOCK, FlowItemCategory::WORLD);

        $this->position = new PositionArgument("position", $position);
        $this->block = new BlockArgument("block", $block);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->position->getName(), $this->block->getName()];
    }

    public function getDetailReplaces(): array {
        return [$this->position->get(), $this->block->get()];
    }

    public function getPosition(): PositionArgument {
        return $this->position;
    }

    public function getBlock(): BlockArgument {
        return $this->block;
    }

    public function isDataValid(): bool {
        return $this->position->isNotEmpty() and $this->block->isNotEmpty();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $position = $this->position->getPosition($source);

        $block = $this->block->getBlock($source);

        $position->world->setBlock($position, $block);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->position->createFormElement($variables),
            $this->block->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->position->set($content[0]);
        $this->block->set($content[1]);
    }

    public function serializeContents(): array {
        return [$this->position->get(), $this->block->get()];
    }
}
