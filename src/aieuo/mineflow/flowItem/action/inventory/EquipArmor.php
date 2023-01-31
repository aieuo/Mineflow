<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\inventory;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\flowItem\base\ItemFlowItem;
use aieuo\mineflow\flowItem\base\ItemFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\mineflow\EntityVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\ItemVariableDropdown;
use aieuo\mineflow\utils\Language;
use pocketmine\entity\Living;
use SOFe\AwaitGenerator\Await;

class EquipArmor extends FlowItem implements EntityFlowItem, ItemFlowItem {
    use EntityFlowItemTrait, ItemFlowItemTrait;
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private array $slots = [
        "action.equipArmor.helmet",
        "action.equipArmor.chestplate",
        "action.equipArmor.leggings",
        "action.equipArmor.boots",
    ];

    public function __construct(string $entity = "", string $item = "", private string $index = "") {
        parent::__construct(self::EQUIP_ARMOR, FlowItemCategory::INVENTORY);

        $this->setEntityVariableName($entity);
        $this->setItemVariableName($item);
    }

    public function getDetailDefaultReplaces(): array {
        return ["entity", "item", "index"];
    }

    public function getDetailReplaces(): array {
        return [$this->getEntityVariableName(), $this->getItemVariableName(), Language::get($this->slots[$this->getIndex()])];
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

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $index = $this->getInt($source->replaceVariables($this->getIndex()), 0, 3);
        $entity = $this->getOnlineEntity($source);
        $item = $this->getItem($source);

        if ($entity instanceof Living) {
            $entity->getArmorInventory()->setItem($index, $item);
        }

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new EntityVariableDropdown($variables, $this->getEntityVariableName()),
            new ItemVariableDropdown($variables, $this->getItemVariableName()),
            new Dropdown("@action.equipArmor.form.index", array_map(fn(string $text) => Language::get($text), $this->slots), (int)$this->getIndex()),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->setEntityVariableName($content[0]);
        $this->setItemVariableName($content[1]);
        $this->setIndex((string)$content[2]);
    }

    public function serializeContents(): array {
        return [$this->getEntityVariableName(), $this->getItemVariableName(), $this->getIndex()];
    }
}
