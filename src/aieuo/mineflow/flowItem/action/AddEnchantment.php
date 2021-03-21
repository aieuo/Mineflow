<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\base\ItemFlowItem;
use aieuo\mineflow\flowItem\base\ItemFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\ItemObjectVariable;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;

class AddEnchantment extends FlowItem implements ItemFlowItem {
    use ItemFlowItemTrait;

    protected $id = self::ADD_ENCHANTMENT;

    protected $name = "action.addEnchant.name";
    protected $detail = "action.addEnchant.detail";
    protected $detailDefaultReplace = ["item", "id", "level"];

    protected $category = Category::ITEM;
    protected $returnValueType = self::RETURN_VARIABLE_NAME;

    /** @var string */
    private $enchantId;
    /** @var string */
    private $enchantLevel;

    public function __construct(string $item = "", string $id = "", string $level = "1") {
        $this->setItemVariableName($item);
        $this->enchantId = $id;
        $this->enchantLevel = $level;
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
        return $this->getItemVariableName() !== "" and $this->enchantId !== "" and $this->enchantLevel !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getItemVariableName(), $this->getEnchantId(), $this->getEnchantLevel()]);
    }

    public function execute(Recipe $source): \Generator {
        $this->throwIfCannotExecute();

        $item = $this->getItem($source);

        $id = $source->replaceVariables($this->getEnchantId());
        if (is_numeric($id)) {
            $enchant = Enchantment::getEnchantment((int)$id);
        } else {
            $enchant = Enchantment::getEnchantmentByName($id);
        }
        if (!($enchant instanceof Enchantment)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.addEnchant.enchant.notFound", [$id]));
        }
        $level = $source->replaceVariables($this->getEnchantLevel());
        $this->throwIfInvalidNumber($level);

        $item->addEnchantment(new EnchantmentInstance($enchant, (int)$level));
        $source->addVariable(new ItemObjectVariable($item, $this->getItemVariableName()));
        yield true;
        return $this->getItemVariableName();
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.form.target.item", "item", $this->getItemVariableName(), true),
            new ExampleInput("@action.addEnchant.form.id", "1", $this->getEnchantId(), true),
            new ExampleNumberInput("@action.addEnchant.form.level", "1", $this->getEnchantLevel(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setItemVariableName($content[0]);
        $this->setEnchantId($content[1]);
        $this->setEnchantLevel($content[2]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getItemVariableName(), $this->getEnchantId(), $this->getEnchantLevel()];
    }

    public function getAddingVariables(): array {
        return [new DummyVariable($this->getItemVariableName(), DummyVariable::ITEM)];
    }
}
