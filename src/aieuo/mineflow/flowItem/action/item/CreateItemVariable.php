<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\item;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\ItemObjectVariable;
use pocketmine\item\LegacyStringToItemParser;
use pocketmine\item\StringToItemParser;

class CreateItemVariable extends FlowItem {

    protected string $id = self::CREATE_ITEM_VARIABLE;

    protected string $name = "action.createItemVariable.name";
    protected string $detail = "action.createItemVariable.detail";
    protected array $detailDefaultReplace = ["item", "id", "count", "name"];

    protected string $category = FlowItemCategory::ITEM;
    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    private string $variableName;
    private string $itemId;
    private string $itemCount;
    private string $itemName;

    public function __construct(string $id = "", string $count = "", string $itemName = "", string $variableName = "item") {
        $this->itemId = $id;
        $this->itemCount = $count;
        $this->itemName = $itemName;
        $this->variableName = $variableName;
    }

    public function setVariableName(string $variableName): void {
        $this->variableName = $variableName;
    }

    public function getVariableName(): string {
        return $this->variableName;
    }

    public function setItemId(string $id): void {
        $this->itemId = $id;
    }

    public function getItemId(): string {
        return $this->itemId;
    }

    public function setItemCount(string $count): void {
        $this->itemCount = $count;
    }

    public function getItemCount(): string {
        return $this->itemCount;
    }

    public function setItemName(string $itemName): void {
        $this->itemName = $itemName;
    }

    public function getItemName(): string {
        return $this->itemName;
    }

    public function isDataValid(): bool {
        return $this->variableName !== "" and $this->itemId !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getVariableName(), $this->getItemId(), $this->getItemCount(), $this->getItemName()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $name = $source->replaceVariables($this->getVariableName());
        $id = $source->replaceVariables($this->getItemId());
        $count = $source->replaceVariables($this->getItemCount());
        $itemName = $source->replaceVariables($this->getItemName());
        try {
            $item = StringToItemParser::getInstance()->parse($id) ?? LegacyStringToItemParser::getInstance()->parse($id);
        } catch (\InvalidArgumentException) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.createItemVariable.item.notFound"));
        }
        if (!empty($count)) {
            $this->throwIfInvalidNumber($count, 0);
            $item->setCount((int)$count);
        } else {
            $item->setCount($item->getMaxStackSize());
        }
        if (!empty($itemName)) {
            $item->setCustomName($itemName);
        }

        $variable = new ItemObjectVariable($item);
        $source->addVariable($name, $variable);
        yield true;
        return $this->getVariableName();
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.createItemVariable.form.id", "1:0", $this->getItemId(), true),
            new ExampleNumberInput("@action.createItemVariable.form.count", "64", $this->getItemCount(), false, 0),
            new ExampleInput("@action.createItemVariable.form.name", "aieuo", $this->getItemName()),
            new ExampleInput("@action.form.resultVariableName", "item", $this->getVariableName(), true),
        ];
    }

    public function parseFromFormData(array $data): array {
        return [$data[3], $data[0], $data[1], $data[2]];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setVariableName($content[0]);
        $this->setItemId($content[1]);
        $this->setItemCount($content[2]);
        $this->setItemName($content[3] ?? "");
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getVariableName(), $this->getItemId(), $this->getItemCount(), $this->getItemName()];
    }

    public function getAddingVariables(): array {
        return [
            $this->getVariableName() => new DummyVariable(DummyVariable::ITEM, $this->getItemId())
        ];
    }
}