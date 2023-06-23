<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\ItemFlowItem;
use aieuo\mineflow\flowItem\base\ItemFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\placeholder\PositionPlaceholder;
use aieuo\mineflow\formAPI\element\mineflow\ItemVariableDropdown;
use SOFe\AwaitGenerator\Await;

class DropItem extends FlowItem implements ItemFlowItem {
    use ItemFlowItemTrait;
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private PositionPlaceholder $position;

    public function __construct(string $position = "", string $item = "") {
        parent::__construct(self::DROP_ITEM, FlowItemCategory::WORLD);

        $this->position = new PositionPlaceholder("position", $position);
        $this->setItemVariableName($item);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->position->getName(), "item"];
    }

    public function getDetailReplaces(): array {
        return [$this->position->get(), $this->getItemVariableName()];
    }

    public function isDataValid(): bool {
        return $this->position->isNotEmpty() and $this->getItemVariableName() !== "";
    }

    public function getPosition(): PositionPlaceholder {
        return $this->position;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $position = $this->position->getPosition($source);

        $item = $this->getItem($source);

        $position->getWorld()->dropItem($position, $item);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->position->createFormElement($variables),
            new ItemVariableDropdown($variables, $this->getItemVariableName()),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->position->set($content[0]);
        $this->setItemVariableName($content[1]);
    }

    public function serializeContents(): array {
        return [$this->position->get(), $this->getItemVariableName()];
    }
}
