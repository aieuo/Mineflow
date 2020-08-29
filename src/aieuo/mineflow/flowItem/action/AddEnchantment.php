<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\ItemFlowItem;
use aieuo\mineflow\flowItem\base\ItemFlowItemTrait;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\ExampleInput;
use aieuo\mineflow\formAPI\element\ExampleNumberInput;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\variable\object\ItemObjectVariable;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\CustomForm;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;

class AddEnchantment extends Action implements ItemFlowItem {
    use ItemFlowItemTrait;

    protected $id = self::ADD_ENCHANTMENT;

    protected $name = "action.addEnchant.name";
    protected $detail = "action.addEnchant.detail";
    protected $detailDefaultReplace = ["item", "id", "level"];

    protected $category = Category::ITEM;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;
    protected $returnValueType = self::RETURN_VARIABLE_NAME;

    /** @var string */
    private $enchantId;
    /** @var string */
    private $enchantLevel;

    public function __construct(string $item = "item", string $id = "", string $level = "1") {
        $this->setItemVariableName($item);
        $this->enchantId = $id;
        $this->enchantLevel = $level;
    }

    public function setEnchantId(string $enchantId) {
        $this->enchantId = $enchantId;
    }

    public function getEnchantId(): string {
        return $this->enchantId;
    }

    public function setEnchantLevel(string $enchantLevel) {
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

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $item = $this->getItem($origin);
        $this->throwIfInvalidItem($item);

        $id = $origin->replaceVariables($this->getEnchantId());
        if (is_numeric($id)) {
            $enchant = Enchantment::getEnchantment((int)$id);
        } else {
            $enchant = Enchantment::getEnchantmentByName($id);
        }
        if (!($enchant instanceof Enchantment)) {
            throw new \UnexpectedValueException(Language::get("action.addEnchant.enchant.notFound"));
        }
        $level = $origin->replaceVariables($this->getEnchantLevel());
        $this->throwIfInvalidNumber($level);

        $item->addEnchantment(new EnchantmentInstance($enchant, (int)$level));
        $origin->addVariable(new ItemObjectVariable($item, $this->getItemVariableName()));
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@flowItem.target.require.item", "item", $default[1] ?? $this->getItemVariableName(), true),
                new ExampleInput("@action.addEnchant.form.id", "1", $default[2] ?? $this->getEnchantId(), true),
                new ExampleNumberInput("@action.addEnchant.form.level", "1", $default[3] ?? $this->getEnchantLevel(), false),
                new CancelToggle()
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        if ($data[2] === "") $data[3] = "1";
        return ["contents" => [$data[1], $data[2], $data[3]], "cancel" => $data[4], "errors" => []];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[2])) throw new \OutOfBoundsException();
        $this->setItemVariableName($content[0]);
        $this->setEnchantId($content[1]);
        $this->setEnchantLevel($content[2]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getItemVariableName(), $this->getEnchantId(), $this->getEnchantLevel()];
    }

    public function getReturnValue(): string {
        return $this->getItemVariableName();
    }
}
