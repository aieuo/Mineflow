<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\argument\ItemArgument;
use aieuo\mineflow\flowItem\argument\PositionArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use SOFe\AwaitGenerator\Await;

class DropItem extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private PositionArgument $position;
    private ItemArgument $item;

    public function __construct(string $position = "", string $item = "") {
        parent::__construct(self::DROP_ITEM, FlowItemCategory::WORLD);

        $this->position = new PositionArgument("position", $position);
        $this->item = new ItemArgument("item", $item);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->position->getName(), $this->item->getName()];
    }

    public function getDetailReplaces(): array {
        return [$this->position->get(), $this->item->get()];
    }

    public function isDataValid(): bool {
        return $this->position->isValid() and $this->item->isValid();
    }

    public function getItem(): ItemArgument {
        return $this->item;
    }

    public function getPosition(): PositionArgument {
        return $this->position;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $position = $this->position->getPosition($source);

        $item = $this->item->getItem($source);

        $position->getWorld()->dropItem($position, $item);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->position->createFormElement($variables),
            $this->item->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->position->set($content[0]);
        $this->item->set($content[1]);
    }

    public function serializeContents(): array {
        return [$this->position->get(), $this->item->get()];
    }
}
