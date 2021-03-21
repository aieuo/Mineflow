<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\ItemFlowItem;
use aieuo\mineflow\flowItem\base\ItemFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\ItemObjectVariable;

class SetItemLore extends FlowItem implements ItemFlowItem {
    use ItemFlowItemTrait;

    protected $id = self::SET_ITEM_LORE;

    protected $name = "action.setLore.name";
    protected $detail = "action.setLore.detail";
    protected $detailDefaultReplace = ["item", "lore"];

    protected $category = Category::ITEM;
    protected $returnValueType = self::RETURN_VARIABLE_NAME;

    /** @var array */
    private $lore;

    public function __construct(string $item = "", string $lore = "") {
        $this->setItemVariableName($item);
        $this->lore = array_filter(array_map("trim", explode(";", $lore)), function (string $t) {
            return $t !== "";
        });
    }

    public function setLore(array $lore): void {
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

    public function execute(Recipe $source): \Generator {
        $this->throwIfCannotExecute();

        $item = $this->getItem($source);

        $lore = array_map(function (string $lore) use ($source) {
            return $source->replaceVariables($lore);
        }, $this->getLore());

        $item->setLore($lore);
        $source->addVariable(new ItemObjectVariable($item, $this->getItemVariableName()));
        yield true;
        return $this->getItemVariableName();
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.form.target.item", "item", $this->getItemVariableName(), true),
            new ExampleInput("@action.setLore.form.lore", "1;aiueo;abc", implode(";", $this->getLore()), false),
        ];
    }

    public function parseFromFormData(array $data): array {
        $lore = array_filter(array_map("trim", explode(";", $data[1])), function (string $t) {
            return $t !== "";
        });
        return ["contents" => [$data[0], $lore]];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setItemVariableName($content[0]);
        $this->setLore($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getItemVariableName(), $this->getLore()];
    }

    public function getAddingVariables(): array {
        return [new DummyVariable($this->getItemVariableName(), DummyVariable::ITEM)];
    }
}
