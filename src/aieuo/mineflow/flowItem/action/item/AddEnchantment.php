<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\item;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\ItemArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\utils\Language;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use SOFe\AwaitGenerator\Await;

class AddEnchantment extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    private ItemArgument $item;

    public function __construct(
        string         $item = "",
        private string $enchantId = "",
        private string $enchantLevel = "1"
    ) {
        parent::__construct(self::ADD_ENCHANTMENT, FlowItemCategory::ITEM);

        $this->item = new ItemArgument("item", $item);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->item->getName(), "id", "world"];
    }

    public function getDetailReplaces(): array {
        return [$this->item->get(), $this->getEnchantId(), $this->getEnchantLevel()];
    }

    public function getItem(): ItemArgument {
        return $this->item;
    }

    public function setEnchantId(string $enchantId): void {
        $this->enchantId = $enchantId;
    }

    public function getEnchantId(): string {
        return $this->enchantId;
    }

    public function setEnchantLevel(string $enchantLevel): void {
        $this->enchantLevel = $enchantLevel;
    }

    public function getEnchantLevel(): string {
        return $this->enchantLevel;
    }

    public function isDataValid(): bool {
        return $this->item->isNotEmpty() and $this->enchantId !== "" and $this->enchantLevel !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $item = $this->item->getItem($source);

        $id = $source->replaceVariables($this->getEnchantId());
        if (is_numeric($id)) {
            $enchant = EnchantmentIdMap::getInstance()->fromId((int)$id);
        } else {
            $enchant = StringToEnchantmentParser::getInstance()->parse($id);
        }
        if (!($enchant instanceof Enchantment)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.addEnchant.enchant.notFound", [$id]));
        }
        $level = $this->getInt($source->replaceVariables($this->getEnchantLevel()));

        $item->addEnchantment(new EnchantmentInstance($enchant, $level));

        yield Await::ALL;
        return $this->item->get();
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new ExampleInput("@action.form.target.item", "item", $this->item->get(), true),
            new ExampleInput("@action.addEnchant.form.id", "1", $this->getEnchantId(), true),
            new ExampleNumberInput("@action.addEnchant.form.level", "1", $this->getEnchantLevel(), true),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->item->set($content[0]);
        $this->setEnchantId($content[1]);
        $this->setEnchantLevel($content[2]);
    }

    public function serializeContents(): array {
        return [$this->item->get(), $this->getEnchantId(), $this->getEnchantLevel()];
    }
}
