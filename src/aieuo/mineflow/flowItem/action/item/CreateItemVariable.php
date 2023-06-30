<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\item;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\EditFormResponseProcessor;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Utils;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\ItemVariable;
use pocketmine\item\LegacyStringToItemParser;
use pocketmine\item\LegacyStringToItemParserException;
use pocketmine\item\StringToItemParser;
use SOFe\AwaitGenerator\Await;

class CreateItemVariable extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    private StringArgument $variableName;
    private StringArgument $itemId;
    private NumberArgument $itemCount;
    private StringArgument $itemName;

    public function __construct(string $itemId = "", int $itemCount = 0, string $itemName = "", string $variableName = "item") {
        parent::__construct(self::CREATE_ITEM_VARIABLE, FlowItemCategory::ITEM);

        $this->setArguments([
            $this->variableName = new StringArgument("item", $variableName, "@action.form.resultVariableName", example: "item"),
            $this->itemId = new StringArgument("id", $itemId, example: "1:0"),
            $this->itemCount = new NumberArgument("count", $itemCount, example: "64", min: 0, optional: true),
            $this->itemName = new StringArgument("name", $itemName, example: "aieuo", optional: true),
        ]);
    }

    public function getVariableName(): StringArgument {
        return $this->variableName;
    }

    public function getItemId(): StringArgument {
        return $this->itemId;
    }

    public function getItemCount(): NumberArgument {
        return $this->itemCount;
    }

    public function getItemName(): StringArgument {
        return $this->itemName;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $this->variableName->getString($source);
        $id = $this->itemId->getString($source);
        $count = $source->replaceVariables($this->itemCount->get());
        $itemName = $this->itemName->getString($source);
        try {
            $item = StringToItemParser::getInstance()->parse($id) ?? LegacyStringToItemParser::getInstance()->parse($id);
        } catch (\InvalidArgumentException|LegacyStringToItemParserException) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.createItem.item.notFound"));
        }
        if (!empty($count)) {
            $item->setCount(Utils::getInt($count, 0));
        } else {
            $item->setCount($item->getMaxStackSize());
        }
        if (!empty($itemName)) {
            $item->setCustomName($itemName);
        }

        $variable = new ItemVariable($item);
        $source->addVariable($name, $variable);

        yield Await::ALL;
        return $this->variableName->get();
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->itemId->createFormElement($variables),
            $this->itemCount->createFormElement($variables),
            $this->itemName->createFormElement($variables),
            $this->variableName->createFormElement($variables),
        ])->response(function (EditFormResponseProcessor $response) {
            $response->rearrange([3, 0, 1, 2]);
        });
    }

    public function getAddingVariables(): array {
        return [
            $this->variableName->get() => new DummyVariable(ItemVariable::class, $this->itemId->get())
        ];
    }
}
