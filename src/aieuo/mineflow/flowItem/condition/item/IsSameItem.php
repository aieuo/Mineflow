<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\condition\item;

use aieuo\mineflow\flowItem\base\ConditionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\ItemFlowItem;
use aieuo\mineflow\flowItem\base\ItemFlowItemTrait;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\FlowItemIds;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\ItemVariableDropdown;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\utils\Language;
use SOFe\AwaitGenerator\GeneratorUtil;

class IsSameItem extends FlowItem implements Condition, ItemFlowItem {
    use ItemFlowItemTrait;
    use ConditionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private const KEY_ITEM1 = "item1";
    private const KEY_ITEM2 = "item2";

    public function __construct(string $item1 = "", string $item2 = "", private bool $checkCompound = false) {
        parent::__construct(FlowItemIds::IS_SAME_ITEM, FlowItemCategory::ITEM);

        $this->setItemVariableName($item1, self::KEY_ITEM1);
        $this->setItemVariableName($item2, self::KEY_ITEM2);
    }

    public function getDetailDefaultReplaces(): array {
        return ["item1", "item2", "tag"];
    }

    public function getDetailReplaces(): array {
        return [
            $this->getItemVariableName(self::KEY_ITEM1),
            $this->getItemVariableName(self::KEY_ITEM2),
            Language::get($this->checkCompound ? "form.yes" : "form.no"),
        ];
    }

    public function isDataValid(): bool {
        return $this->getItemVariableName(self::KEY_ITEM1) !== "" and $this->getItemVariableName(self::KEY_ITEM2) !== "";
    }

    public function getCheckCompound(): bool {
        return $this->checkCompound;
    }

    public function setCheckCompound(bool $checkCompound): void {
        $this->checkCompound = $checkCompound;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $item1 = $this->getItem($source, self::KEY_ITEM1);
        $item2 = $this->getItem($source, self::KEY_ITEM2);

        yield from GeneratorUtil::empty();
        return $item1->equals($item2, checkCompound: $this->checkCompound);
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new ItemVariableDropdown($variables, $this->getItemVariableName(self::KEY_ITEM1), "@action.form.target.item (1)"),
            new ItemVariableDropdown($variables, $this->getItemVariableName(self::KEY_ITEM2), "@action.form.target.item (2)"),
            new Toggle("@condition.isSameItem.form.checkCompound", $this->getCheckCompound()),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->setItemVariableName($content[0], self::KEY_ITEM1);
        $this->setItemVariableName($content[1], self::KEY_ITEM2);
        $this->setCheckCompound($content[2]);
    }

    public function serializeContents(): array {
        return [$this->getItemVariableName(self::KEY_ITEM1), $this->getItemVariableName(self::KEY_ITEM2), $this->getCheckCompound()];
    }
}