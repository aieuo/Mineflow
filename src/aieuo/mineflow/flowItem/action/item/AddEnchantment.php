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

    public function __construct(string $item = "", string $enchantId = "", int $enchantLevel = 1) {
        parent::__construct(self::ADD_ENCHANTMENT, FlowItemCategory::ITEM);

        $this->setArguments([
            ItemArgument::create("item", $item),
            StringArgument::create("id", $enchantId)->example("1"),
            NumberArgument::create("level", $enchantLevel)->example("1"),
        ]);
    }

    public function getItem(): ItemArgument {
        return $this->getArguments()[0];
    }

    public function getEnchantId(): StringArgument {
        return $this->getArguments()[1];
    }

    public function getEnchantLevel(): NumberArgument {
        return $this->getArguments()[2];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $item = $this->getItem()->getItem($source);

        $id = $this->getEnchantId()->getString($source);
        if (is_numeric($id)) {
            $enchant = EnchantmentIdMap::getInstance()->fromId((int)$id);
        } else {
            $enchant = StringToEnchantmentParser::getInstance()->parse($id);
        }
        if (!($enchant instanceof Enchantment)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.addEnchant.enchant.notFound", [$id]));
        }
        $level = $this->getEnchantLevel()->getInt($source);

        $item->addEnchantment(new EnchantmentInstance($enchant, $level));

        yield Await::ALL;
        return (string)$this->getItem();
    }
}
