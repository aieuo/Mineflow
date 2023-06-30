<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\inventory;

use aieuo\mineflow\flowItem\argument\EntityArgument;
use aieuo\mineflow\flowItem\argument\ItemArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\utils\Language;
use pocketmine\entity\Living;
use SOFe\AwaitGenerator\Await;

class EquipArmor extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private array $slots = [
        "action.equipArmor.helmet",
        "action.equipArmor.chestplate",
        "action.equipArmor.leggings",
        "action.equipArmor.boots",
    ];

    private ItemArgument $item;
    private EntityArgument $entity;

    public function __construct(string $entity = "", string $item = "", private string $index = "") {
        parent::__construct(self::EQUIP_ARMOR, FlowItemCategory::INVENTORY);

        $this->entity = new EntityArgument("entity", $entity);
        $this->item = new ItemArgument("item", $item);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->entity->getName(), $this->item->getName(), "index"];
    }

    public function getDetailReplaces(): array {
        return [$this->entity->get(), $this->item->get(), Language::get($this->slots[$this->getIndex()])];
    }

    public function getEntity(): EntityArgument {
        return $this->entity;
    }

    public function getItem(): ItemArgument {
        return $this->item;
    }

    public function setIndex(string $health): void {
        $this->index = $health;
    }

    public function getIndex(): string {
        return $this->index;
    }

    public function isDataValid(): bool {
        return $this->entity->isValid() and $this->item->isValid() and $this->index->isValid();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $index = $this->getInt($source->replaceVariables($this->getIndex()), 0, 3);
        $entity = $this->entity->getOnlineEntity($source);
        $item = $this->item->getItem($source);

        if ($entity instanceof Living) {
            $entity->getArmorInventory()->setItem($index, $item);
        }

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
           $this->entity->createFormElement($variables),
            $this->item->createFormElement($variables),
            new Dropdown("@action.equipArmor.form.index", array_map(fn(string $text) => Language::get($text), $this->slots), (int)$this->getIndex()),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->entity->set($content[0]);
        $this->item->set($content[1]);
        $this->setIndex((string)$content[2]);
    }

    public function serializeContents(): array {
        return [$this->entity->get(), $this->item->get(), $this->getIndex()];
    }
}
