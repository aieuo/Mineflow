<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\item;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\argument\ItemArgument;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use pocketmine\world\format\io\GlobalItemDataHandlers;
use SOFe\AwaitGenerator\Await;

class SetItemDamage extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    private ItemArgument $item;

    public function __construct(string $item = "", private string $damage = "") {
        parent::__construct(self::SET_ITEM_DAMAGE, FlowItemCategory::ITEM);

        $this->item = new ItemArgument("item", $item);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->item->getName(), "damage"];
    }

    public function getDetailReplaces(): array {
        return [$this->item->get(), $this->getDamage()];
    }

    public function getItem(): ItemArgument {
        return $this->item;
    }

    public function setDamage(string $damage): void {
        $this->damage = $damage;
    }

    public function getDamage(): string {
        return $this->damage;
    }

    public function isDataValid(): bool {
        return $this->item->isNotEmpty() and $this->damage !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $damage = $this->getInt($source->replaceVariables($this->getDamage()), 0);
        $item = $this->item->getItem($source);

        $itemType = GlobalItemDataHandlers::getSerializer()->serializeType($item);
        $itemStack = GlobalItemDataHandlers::getUpgrader()->upgradeItemTypeDataString($itemType->getName(), $damage, $item->getCount(), $item->getNamedTag());
        $newItem = GlobalItemDataHandlers::getDeserializer()->deserializeStack($itemStack);
        $this->item->getItemVariable($source)->setItem($newItem);

        yield Await::ALL;
        return $this->item->get();
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->item->createFormElement($variables),
            new ExampleNumberInput("@action.setDamage.form.damage", "0", $this->getDamage(), true, 0),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->item->set($content[0]);
        $this->setDamage($content[1]);
    }

    public function serializeContents(): array {
        return [$this->item->get(), $this->getDamage()];
    }
}
