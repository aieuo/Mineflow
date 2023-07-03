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

    public function __construct(string $itemId = "", int $itemCount = 0, string $itemName = "", string $variableName = "item") {
        parent::__construct(self::CREATE_ITEM_VARIABLE, FlowItemCategory::ITEM);

        $this->setArguments([
            new StringArgument("item", $variableName, "@action.form.resultVariableName", example: "item"),
            new StringArgument("id", $itemId, example: "1:0"),
            new NumberArgument("count", $itemCount, example: "64", min: 0, optional: true),
            new StringArgument("name", $itemName, example: "aieuo", optional: true),
        ]);
    }

    public function getVariableName(): StringArgument {
        return $this->getArguments()[0];
    }

    public function getItemId(): StringArgument {
        return $this->getArguments()[1];
    }

    public function getItemCount(): NumberArgument {
        return $this->getArguments()[2];
    }

    public function getItemName(): StringArgument {
        return $this->getArguments()[3];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $this->getVariableName()->getString($source);
        $id = $this->getItemId()->getString($source);
        $count = $source->replaceVariables($this->getItemCount()->get());
        $itemName = $this->getItemName()->getString($source);
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
        return $this->getVariableName()->get();
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->getItemId()->createFormElement($variables),
            $this->getItemCount()->createFormElement($variables),
            $this->getItemName()->createFormElement($variables),
            $this->getVariableName()->createFormElement($variables),
        ])->response(function (EditFormResponseProcessor $response) {
            $response->rearrange([3, 0, 1, 2]);
        });
    }

    public function getAddingVariables(): array {
        return [
            $this->getVariableName()->get() => new DummyVariable(ItemVariable::class, $this->getItemId()->get())
        ];
    }
}
