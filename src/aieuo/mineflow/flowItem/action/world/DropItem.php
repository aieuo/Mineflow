<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\ItemFlowItem;
use aieuo\mineflow\flowItem\base\ItemFlowItemTrait;
use aieuo\mineflow\flowItem\base\PositionFlowItem;
use aieuo\mineflow\flowItem\base\PositionFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\ItemVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\PositionVariableDropdown;
use aieuo\mineflow\utils\Language;
use SOFe\AwaitGenerator\Await;

class DropItem extends FlowItem implements PositionFlowItem, ItemFlowItem {
    use PositionFlowItemTrait, ItemFlowItemTrait;
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    public function __construct(string $position = "", string $item = "") {
        parent::__construct(self::DROP_ITEM, FlowItemCategory::WORLD);

        $this->setPositionVariableName($position);
        $this->setItemVariableName($item);
    }

    public function getDetailDefaultReplaces(): array {
        return ["position", "item"];
    }

    public function getDetailReplaces(): array {
        return [$this->getPositionVariableName(), $this->getItemVariableName()];
    }

    public function isDataValid(): bool {
        return $this->getPositionVariableName() !== "" and $this->getItemVariableName() !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $position = $this->getPosition($source);

        $item = $this->getItem($source);

        $position->getWorld()->dropItem($position, $item);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new PositionVariableDropdown($variables, $this->getPositionVariableName()),
            new ItemVariableDropdown($variables, $this->getItemVariableName()),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->setPositionVariableName($content[0]);
        $this->setItemVariableName($content[1]);
    }

    public function serializeContents(): array {
        return [$this->getPositionVariableName(), $this->getItemVariableName()];
    }
}
