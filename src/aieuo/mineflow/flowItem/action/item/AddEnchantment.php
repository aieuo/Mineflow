<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\item;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\ItemArgument;
use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use SOFe\AwaitGenerator\Await;
use function is_numeric;

class AddEnchantment extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    private ItemArgument $item;
    private StringArgument $enchantId;
    private NumberArgument $enchantLevel;

    public function __construct(string $item = "", string $enchantId = "", int $enchantLevel = 1) {
        parent::__construct(self::ADD_ENCHANTMENT, FlowItemCategory::ITEM);

        $this->setArguments([
            $this->item = new ItemArgument("item", $item),
            $this->enchantId = new StringArgument("id", $enchantId, example: "1"),
            $this->enchantLevel = new NumberArgument("level", $enchantLevel, example: "1"),
        ]);
    }

    public function getItem(): ItemArgument {
        return $this->item;
    }

    public function getEnchantId(): StringArgument {
        return $this->enchantId;
    }

    public function getEnchantLevel(): NumberArgument {
        return $this->enchantLevel;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $item = $this->item->getItem($source);

        $id = $this->enchantId->getString($source);
        if (is_numeric($id)) {
            $enchant = EnchantmentIdMap::getInstance()->fromId((int)$id);
        } else {
            $enchant = StringToEnchantmentParser::getInstance()->parse($id);
        }
        if (!($enchant instanceof Enchantment)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.addEnchant.enchant.notFound", [$id]));
        }
        $level = $this->enchantLevel->getInt($source);

        $item->addEnchantment(new EnchantmentInstance($enchant, $level));

        yield Await::ALL;
        return $this->item->get();
    }
}
