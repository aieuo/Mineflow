<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\ItemFlowItem;
use aieuo\mineflow\flowItem\base\ItemFlowItemTrait;
use aieuo\mineflow\flowItem\base\PositionFlowItem;
use aieuo\mineflow\flowItem\base\PositionFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\mineflow\ItemVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\PositionVariableDropdown;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;

class DropItem extends FlowItem implements PositionFlowItem, ItemFlowItem {
    use PositionFlowItemTrait, ItemFlowItemTrait;

    protected $id = self::DROP_ITEM;

    protected $name = "action.dropItem.name";
    protected $detail = "action.dropItem.detail";
    protected $detailDefaultReplace = ["position", "item"];

    protected $category = Category::LEVEL;

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

    public function execute(Recipe $origin): \Generator {
        $this->throwIfCannotExecute();

        $position = $this->getPosition($origin);

        $item = $this->getItem($origin);

        $position->getLevelNonNull()->dropItem($position, $item);
        yield true;
    }

    public function getEditForm(array $variables = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new PositionVariableDropdown($variables, $this->getPositionVariableName()),
                new ItemVariableDropdown($variables, $this->getItemVariableName()),
                new CancelToggle()
            ]);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], $data[2]], "cancel" => $data[3]];
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