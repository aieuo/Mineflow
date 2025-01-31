<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\item;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\editor\MainFlowItemEditor;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Utils;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\ItemVariable;
use pocketmine\item\LegacyStringToItemParser;
use pocketmine\item\LegacyStringToItemParserException;
use pocketmine\item\StringToItemParser;
use aieuo\mineflow\libs\_6b4cfdc0a11de6c9\SOFe\AwaitGenerator\Await;

class CreateItemVariable extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    public function __construct(string $itemId = "", int $itemCount = 0, string $itemName = "", string $variableName = "item") {
        parent::__construct(self::CREATE_ITEM_VARIABLE, FlowItemCategory::ITEM);

        $this->setArguments([
            StringArgument::create("item", $variableName, "@action.form.resultVariableName")->example("item"),
            StringArgument::create("id", $itemId)->example("1:0"),
            NumberArgument::create("count", $itemCount)->min(0)->optional()->example("64"),
            StringArgument::create("name", $itemName)->optional()->example("aieuo"),
        ]);
    }

    public function getVariableName(): StringArgument {
        return $this->getArgument("item");
    }

    public function getItemId(): StringArgument {
        return $this->getArgument("id");
    }

    public function getItemCount(): NumberArgument {
        return $this->getArgument("count");
    }

    public function getItemName(): StringArgument {
        return $this->getArgument("name");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $this->getVariableName()->getString($source);
        $id = $this->getItemId()->getString($source);
        $count = $this->getItemCount()->getRawString();
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
        return (string)$this->getVariableName();
    }

    public function getAddingVariables(): array {
        return [
            (string)$this->getVariableName() => new DummyVariable(ItemVariable::class, (string)$this->getItemId())
        ];
    }

    public function getEditors(): array {
        return [
            new MainFlowItemEditor($this, [
                $this->getItemId(),
                $this->getItemCount(),
                $this->getItemName(),
                $this->getVariableName(),
            ]),
        ];
    }
}