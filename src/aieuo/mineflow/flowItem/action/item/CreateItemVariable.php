<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\item;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\EditFormResponseProcessor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\ItemVariable;
use pocketmine\item\LegacyStringToItemParser;
use pocketmine\item\StringToItemParser;
use SOFe\AwaitGenerator\Await;

class CreateItemVariable extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    public function __construct(
        private string $itemId = "",
        private string $itemCount = "",
        private string $itemName = "",
        private string $variableName = "item"
    ) {
        parent::__construct(self::CREATE_ITEM_VARIABLE, FlowItemCategory::ITEM);
    }

    public function getDetailDefaultReplaces(): array {
        return ["item", "id", "count", "name"];
    }

    public function getDetailReplaces(): array {
        return [$this->getVariableName(), $this->getItemId(), $this->getItemCount(), $this->getItemName()];
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

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $source->replaceVariables($this->getVariableName());
        $id = $source->replaceVariables($this->getItemId());
        $count = $source->replaceVariables($this->getItemCount());
        $itemName = $source->replaceVariables($this->getItemName());
        try {
            $item = StringToItemParser::getInstance()->parse($id) ?? LegacyStringToItemParser::getInstance()->parse($id);
        } catch (\InvalidArgumentException) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.createItem.item.notFound"));
        }
        if (!empty($count)) {
            $item->setCount($this->getInt($count, 0));
        } else {
            $item->setCount($item->getMaxStackSize());
        }
        if (!empty($itemName)) {
            $item->setCustomName($itemName);
        }

        $variable = new ItemVariable($item);
        $source->addVariable($name, $variable);

        yield Await::ALL;
        return $this->getVariableName();
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new ExampleInput("@action.createItem.form.id", "1:0", $this->getItemId(), true),
            new ExampleNumberInput("@action.createItem.form.count", "64", $this->getItemCount(), false, 0),
            new ExampleInput("@action.createItem.form.name", "aieuo", $this->getItemName()),
            new ExampleInput("@action.form.resultVariableName", "item", $this->getVariableName(), true),
        ])->response(function (EditFormResponseProcessor $response) {
            $response->rearrange([3, 0, 1, 2]);
        });
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
            $this->getVariableName() => new DummyVariable(ItemVariable::class, $this->getItemId())
        ];
    }
}
