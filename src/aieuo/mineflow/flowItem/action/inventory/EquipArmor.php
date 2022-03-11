<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\inventory;

use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\flowItem\base\ItemFlowItem;
use aieuo\mineflow\flowItem\base\ItemFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\mineflow\EntityVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\ItemVariableDropdown;
use aieuo\mineflow\utils\Language;
use pocketmine\entity\Living;

class EquipArmor extends FlowItem implements EntityFlowItem, ItemFlowItem {
    use EntityFlowItemTrait, ItemFlowItemTrait;

    protected string $name = "action.equipArmor.name";
    protected string $detail = "action.equipArmor.detail";
    protected array $detailDefaultReplace = ["entity", "item", "index"];

    private string $index;

    private array $slots = [
        "action.equipArmor.helmet",
        "action.equipArmor.chestplate",
        "action.equipArmor.leggings",
        "action.equipArmor.boots",
    ];

    public function __construct(string $entity = "", string $item = "", string $index = "") {
        parent::__construct(self::EQUIP_ARMOR, FlowItemCategory::INVENTORY);

        $this->setEntityVariableName($entity);
        $this->setItemVariableName($item);
        $this->index = $index;
    }

    public function setIndex(string $health): void {
        $this->index = $health;
    }

    public function getIndex(): string {
        return $this->index;
    }

    public function isDataValid(): bool {
        return $this->getEntityVariableName() !== "" and $this->getItemVariableName() !== "" and $this->index !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getEntityVariableName(), $this->getItemVariableName(), Language::get($this->slots[$this->getIndex()])]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $index = $source->replaceVariables($this->getIndex());

        $this->throwIfInvalidNumber($index, 0, 3);

        $entity = $this->getEntity($source);
        $this->throwIfInvalidEntity($entity);

        $item = $this->getItem($source);

        if ($entity instanceof Living) {
            $entity->getArmorInventory()->setItem((int)$index, $item);
        }
        yield true;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new EntityVariableDropdown($variables, $this->getEntityVariableName()),
            new ItemVariableDropdown($variables, $this->getItemVariableName()),
            new Dropdown("@action.equipArmor.form.index", array_map(fn(string $text) => Language::get($text), $this->slots), (int)$this->getIndex()),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setEntityVariableName($content[0]);
        $this->setItemVariableName($content[1]);
        $this->setIndex((string)$content[2]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getEntityVariableName(), $this->getItemVariableName(), $this->getIndex()];
    }
}