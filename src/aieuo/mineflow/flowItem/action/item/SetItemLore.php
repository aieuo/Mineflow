<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\item;

use aieuo\mineflow\flowItem\base\ItemFlowItem;
use aieuo\mineflow\flowItem\base\ItemFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\ItemVariableDropdown;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;

class SetItemLore extends FlowItem implements ItemFlowItem {
    use ItemFlowItemTrait;

    protected string $id = self::SET_ITEM_LORE;

    protected string $name = "action.setLore.name";
    protected string $detail = "action.setLore.detail";
    protected array $detailDefaultReplace = ["item", "lore"];

    protected string $category = Category::ITEM;
    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    private array $lore;

    public function __construct(string $item = "", string $lore = "") {
        $this->setItemVariableName($item);
        $this->lore = array_filter(array_map("trim", explode(";", $lore)), fn(string $t) => $t !== "");
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
        return Language::get($this->detail, [$this->getItemVariableName(), implode(";", $this->getLore())]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $item = $this->getItem($source);

        $lore = array_map(fn(string $lore) => $source->replaceVariables($lore), $this->getLore());

        $item->setLore($lore);
        yield FlowItemExecutor::CONTINUE;
        return $this->getItemVariableName();
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ItemVariableDropdown($variables, $this->getItemVariableName()),
            new ExampleInput("@action.setLore.form.lore", "1;aiueo;abc", implode(";", $this->getLore()), false),
        ];
    }

    public function parseFromFormData(array $data): array {
        $lore = array_filter(array_map("trim", explode(";", $data[1])), fn(string $t) => $t !== "");
        return [$data[0], $lore];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setItemVariableName($content[0]);
        $this->setLore($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getItemVariableName(), $this->getLore()];
    }
}
