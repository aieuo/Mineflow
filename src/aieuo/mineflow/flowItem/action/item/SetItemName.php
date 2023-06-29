<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\item;

use aieuo\mineflow\flowItem\argument\ItemArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use SOFe\AwaitGenerator\Await;

class SetItemName extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    private ItemArgument $item;
    private StringArgument $itemName;

    public function __construct(string $item = "", string $itemName = "") {
        parent::__construct(self::SET_ITEM_NAME, FlowItemCategory::ITEM);

        $this->item = new ItemArgument("item", $item);
        $this->itemName = new StringArgument("name", $itemName, "@action.createItem.form.name", example: "aieuo");
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->item->getName(), "name"];
    }

    public function getDetailReplaces(): array {
        return [$this->item->get(), $this->itemName->get()];
    }

    public function getItem(): ItemArgument {
        return $this->item;
    }

    public function getItemName(): StringArgument {
        return $this->itemName;
    }

    public function isDataValid(): bool {
        return $this->item->isNotEmpty();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $this->itemName->getString($source);

        $item = $this->item->getItem($source);

        $item->setCustomName($name);

        yield Await::ALL;
        return $this->item->get();
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->item->createFormElement($variables),
            $this->itemName->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->item->set($content[0]);
        $this->itemName->set($content[1]);
    }

    public function serializeContents(): array {
        return [$this->item->get(), $this->itemName->get()];
    }
}
