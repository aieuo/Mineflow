<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\condition\item;

use aieuo\mineflow\flowItem\argument\ItemArgument;
use aieuo\mineflow\flowItem\base\ConditionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\FlowItemIds;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\utils\Language;
use SOFe\AwaitGenerator\GeneratorUtil;

class IsSameItem extends FlowItem implements Condition {
    use ConditionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private ItemArgument $item1;
    private ItemArgument $item2;

    public function __construct(string $item1 = "", string $item2 = "", private bool $checkCompound = false) {
        parent::__construct(FlowItemIds::IS_SAME_ITEM, FlowItemCategory::ITEM);

        $this->item1 = new ItemArgument("item1", $item1, "@action.form.target.item (1)");
        $this->item2 = new ItemArgument("item2", $item2, "@action.form.target.item (2)");
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->item1->getName(), $this->item2->getName(), "tag"];
    }

    public function getDetailReplaces(): array {
        return [
            $this->item1->get(),
            $this->item2->get(),
            Language::get($this->checkCompound ? "form.yes" : "form.no"),
        ];
    }

    public function isDataValid(): bool {
        return $this->item1->isValid() and $this->item2->isValid();
    }

    public function getItem1(): ItemArgument {
        return $this->item1;
    }

    public function getItem2(): ItemArgument {
        return $this->item2;
    }

    public function getCheckCompound(): bool {
        return $this->checkCompound;
    }

    public function setCheckCompound(bool $checkCompound): void {
        $this->checkCompound = $checkCompound;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $item1 = $this->item1->getItem($source);
        $item2 = $this->item2->getItem($source);

        yield from GeneratorUtil::empty();
        return $item1->equals($item2, checkCompound: $this->checkCompound);
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->item1->createFormElement($variables),
            $this->item2->createFormElement($variables),
            new Toggle("@condition.isSameItem.form.checkCompound", $this->getCheckCompound()),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->item1->set($content[0]);
        $this->item2->set($content[1]);
        $this->setCheckCompound($content[2]);
    }

    public function serializeContents(): array {
        return [$this->item1->get(), $this->item2->get(), $this->getCheckCompound()];
    }
}
