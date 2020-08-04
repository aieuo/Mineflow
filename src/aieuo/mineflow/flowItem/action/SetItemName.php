<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\ItemFlowItem;
use aieuo\mineflow\flowItem\base\ItemFlowItemTrait;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\variable\object\ItemObjectVariable;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Toggle;

class SetItemName extends Action implements ItemFlowItem {
    use ItemFlowItemTrait;

    protected $id = self::SET_ITEM_NAME;

    protected $name = "action.setItemName.name";
    protected $detail = "action.setItemName.detail";
    protected $detailDefaultReplace = ["item", "name"];

    protected $category = Category::ITEM;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;
    protected $returnValueType = self::RETURN_VARIABLE_NAME;

    /** @var string */
    private $itemName;

    public function __construct(string $item = "item", string $itemName = "") {
        $this->setItemVariableName($item);
        $this->itemName = $itemName;
    }

    public function setItemName(string $itemName) {
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

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $name = $origin->replaceVariables($this->getItemName());

        $item = $this->getItem($origin);
        $this->throwIfInvalidItem($item);

        $item->setCustomName($name);
        $origin->addVariable(new ItemObjectVariable($item, $this->getItemVariableName()));
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@flowItem.target.require.item", Language::get("form.example", ["item"]), $default[1] ?? $this->getItemVariableName()),
                new Input("@action.createItemVariable.form.name", Language::get("form.example", ["aieuo"]), $default[2] ?? $this->getItemName()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[1] === "") $data[1] = "item";
        if ($data[2] === "") {
            $errors[] = ["@form.insufficient", 2];
        }
        return ["contents" => [$data[1], $data[2]], "cancel" => $data[3], "errors" => $errors];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[1])) throw new \OutOfBoundsException();
        $this->setItemVariableName($content[0]);
        $this->setItemName($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getItemVariableName(), $this->getItemName()];
    }

    public function getReturnValue(): string {
        return $this->getItemVariableName();
    }
}
