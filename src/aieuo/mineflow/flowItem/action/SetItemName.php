<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\ItemFlowItem;
use aieuo\mineflow\flowItem\base\ItemFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\mineflow\CancelToggle;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\ItemObjectVariable;

class SetItemName extends FlowItem implements ItemFlowItem {
    use ItemFlowItemTrait;

    protected $id = self::SET_ITEM_NAME;

    protected $name = "action.setItemName.name";
    protected $detail = "action.setItemName.detail";
    protected $detailDefaultReplace = ["item", "name"];

    protected $category = Category::ITEM;
    protected $returnValueType = self::RETURN_VARIABLE_NAME;

    /** @var string */
    private $itemName;

    public function __construct(string $item = "", string $itemName = "") {
        $this->setItemVariableName($item);
        $this->itemName = $itemName;
    }

    public function setItemName(string $itemName): void {
        $this->itemName = $itemName;
    }

    public function getItemName(): string {
        return $this->itemName;
    }

    public function isDataValid(): bool {
        return $this->getItemVariableName() !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getItemVariableName(), $this->getItemName()]);
    }

    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $name = $origin->replaceVariables($this->getItemName());

        $item = $this->getItem($origin);
        $this->throwIfInvalidItem($item);

        $item->setCustomName($name);
        $origin->addVariable(new ItemObjectVariable($item, $this->getItemVariableName()));
        yield true;
        return $this->getItemVariableName();
    }

    public function getEditForm(array $variables = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@flowItem.target.require.item", "item", $this->getItemVariableName(), true),
                new ExampleInput("@action.createItemVariable.form.name", "aieuo", $this->getItemName(), true),
                new CancelToggle()
            ]);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], $data[2]], "cancel" => $data[3]];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setItemVariableName($content[0]);
        $this->setItemName($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getItemVariableName(), $this->getItemName()];
    }

    public function getAddingVariables(): array {
        return [new DummyVariable($this->getItemVariableName(), DummyVariable::ITEM)];
    }
}
