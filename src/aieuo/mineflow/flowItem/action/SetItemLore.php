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

class SetItemLore extends Action implements ItemFlowItem {
    use ItemFlowItemTrait;

    protected $id = self::SET_ITEM_LORE;

    protected $name = "action.setLore.name";
    protected $detail = "action.setLore.detail";
    protected $detailDefaultReplace = ["item", "lore"];

    protected $category = Category::ITEM;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;
    protected $returnValueType = self::RETURN_VARIABLE_NAME;

    /** @var array */
    private $lore;

    public function __construct(string $item = "item", string $lore = "") {
        $this->setItemVariableName($item);
        $this->lore = array_filter(array_map("trim", explode(";", $lore)), function (string $t) { return $t !== ""; });
    }

    public function setLore(array $lore) {
        $this->lore = $lore;
    }

    public function getLore(): array {
        return $this->lore;
    }

    public function isDataValid(): bool {
        return $this->getItemVariableName() !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getItemVariableName(), implode(";", $this->getLore())]);
    }

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $item = $this->getItem($origin);
        $this->throwIfInvalidItem($item);

        $lore = array_map(function (string $lore) use ($origin) {
            return $origin->replaceVariables($lore);
        }, $this->getLore());

        $item->setLore($lore);
        $origin->addVariable(new ItemObjectVariable($item, $this->getItemVariableName()));
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@flowItem.target.require.item", Language::get("form.example", ["item"]), $default[1] ?? $this->getItemVariableName()),
                new Input("@action.setLore.form.lore", Language::get("form.example", ["1;aiueo;abc"]), $default[2] ?? implode(";", $this->getLore())),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[1] === "") $data[1] = "item";
        $lore = array_filter(array_map("trim", explode(";", $data[2])), function (string $t) { return $t !== ""; });
        return ["contents" => [$data[1], $lore], "cancel" => $data[3], "errors" => $errors];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[1])) throw new \OutOfBoundsException();
        $this->setItemVariableName($content[0]);
        $this->setLore($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getItemVariableName(), $this->getLore()];
    }

    public function getReturnValue(): string {
        return $this->getItemVariableName();
    }
}
