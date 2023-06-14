<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\item;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\ItemFlowItem;
use aieuo\mineflow\flowItem\base\ItemFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\formAPI\element\mineflow\ItemVariableDropdown;
use pocketmine\world\format\io\GlobalItemDataHandlers;
use SOFe\AwaitGenerator\Await;

class SetItemDamage extends FlowItem implements ItemFlowItem {
    use ItemFlowItemTrait;
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    public function __construct(string $item = "", private string $damage = "") {
        parent::__construct(self::SET_ITEM_DAMAGE, FlowItemCategory::ITEM);

        $this->setItemVariableName($item);
    }

    public function getDetailDefaultReplaces(): array {
        return ["item", "damage"];
    }

    public function getDetailReplaces(): array {
        return [$this->getItemVariableName(), $this->getDamage()];
    }

    public function setDamage(string $damage): void {
        $this->damage = $damage;
    }

    public function getDamage(): string {
        return $this->damage;
    }

    public function isDataValid(): bool {
        return $this->getItemVariableName() !== "" and $this->damage !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $damage = $this->getInt($source->replaceVariables($this->getDamage()), 0);
        $item = $this->getItem($source);

        $itemType = GlobalItemDataHandlers::getSerializer()->serializeType($item);
        $itemStack = GlobalItemDataHandlers::getUpgrader()->upgradeItemTypeDataString($itemType->getName(), $damage, $item->getCount(), $item->getNamedTag());
        $newItem = GlobalItemDataHandlers::getDeserializer()->deserializeStack($itemStack);
        $this->getItemVariable($source)->setItem($newItem);

        yield Await::ALL;
        return $this->getItemVariableName();
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new ItemVariableDropdown($variables, $this->getItemVariableName()),
            new ExampleNumberInput("@action.setDamage.form.damage", "0", $this->getDamage(), true, 0),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->setItemVariableName($content[0]);
        $this->setDamage($content[1]);
    }

    public function serializeContents(): array {
        return [$this->getItemVariableName(), $this->getDamage()];
    }
}
