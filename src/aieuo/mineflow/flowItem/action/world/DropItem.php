<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\base\ItemFlowItem;
use aieuo\mineflow\flowItem\base\ItemFlowItemTrait;
use aieuo\mineflow\flowItem\base\PositionFlowItem;
use aieuo\mineflow\flowItem\base\PositionFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ItemVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\PositionVariableDropdown;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;

class DropItem extends FlowItem implements PositionFlowItem, ItemFlowItem {
    use PositionFlowItemTrait, ItemFlowItemTrait;

    protected $id = self::DROP_ITEM;

    protected $name = "action.dropItem.name";
    protected $detail = "action.dropItem.detail";
    protected $detailDefaultReplace = ["position", "item"];

    protected $category = Category::WORLD;

    public function __construct(string $position = "", string $item = "") {
        $this->setPositionVariableName($position);
        $this->setItemVariableName($item);
    }

    public function isDataValid(): bool {
        return $this->getPositionVariableName() !== "" and $this->getItemVariableName() !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPositionVariableName(), $this->getItemVariableName()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $position = $this->getPosition($source);

        $item = $this->getItem($source);

        $position->getLevelNonNull()->dropItem($position, $item);
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