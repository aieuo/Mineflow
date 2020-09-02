<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\ItemFlowItem;
use aieuo\mineflow\flowItem\base\ItemFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\ExampleInput;
use aieuo\mineflow\formAPI\element\ExampleNumberInput;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\ItemObjectVariable;

class SetItemCount extends FlowItem implements ItemFlowItem {
    use ItemFlowItemTrait;

    protected $id = self::SET_ITEM_COUNT;

    protected $name = "action.setItemCount.name";
    protected $detail = "action.setItemCount.detail";
    protected $detailDefaultReplace = ["item", "count"];

    protected $category = Category::ITEM;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;
    protected $returnValueType = self::RETURN_VARIABLE_NAME;

    /** @var string */
    private $count;

    public function __construct(string $item = "item", string $count = "") {
        $this->setItemVariableName($item);
        $this->count = $count;
    }

    public function setCount(string $count) {
        $this->count = $count;
    }

    public function getCount(): string {
        return $this->count;
    }

    public function isDataValid(): bool {
        return $this->getItemVariableName() !== "" and $this->count !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getItemVariableName(), $this->getCount()]);
    }

    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $count = $origin->replaceVariables($this->getCount());
        $this->throwIfInvalidNumber($count, 0);

        $item = $this->getItem($origin);
        $this->throwIfInvalidItem($item);

        $item->setCount((int)$count);
        $origin->addVariable(new ItemObjectVariable($item, $this->getItemVariableName()));
        yield true;
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@flowItem.target.require.item", "item", $default[1] ?? $this->getItemVariableName(), true),
                new ExampleNumberInput("@action.createItemVariable.form.count", "64", $default[2] ?? $this->getCount(), true, 0),
                new CancelToggle()
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], $data[2]], "cancel" => $data[3], "errors" => []];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setItemVariableName($content[0]);
        $this->setCount($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getItemVariableName(), $this->getCount()];
    }

    public function getReturnValue(): string {
        return $this->getItemVariableName();
    }
}
