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
use aieuo\mineflow\formAPI\element\mineflow\ItemVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\PositionVariableDropdown;

class DropItem extends FlowItem implements PositionFlowItem, ItemFlowItem {
    use PositionFlowItemTrait, ItemFlowItemTrait;
    use ActionNameWithMineflowLanguage;

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

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $position = $this->getPosition($source);

        $item = $this->getItem($source);

        $position->getWorld()->dropItem($position, $item);
        yield true;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new PositionVariableDropdown($variables, $this->getPositionVariableName()),
            new ItemVariableDropdown($variables, $this->getItemVariableName()),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setPositionVariableName($content[0]);
        $this->setItemVariableName($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPositionVariableName(), $this->getItemVariableName()];
    }
}
