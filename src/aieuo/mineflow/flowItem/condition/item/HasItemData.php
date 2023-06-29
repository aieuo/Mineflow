<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\item;

use aieuo\mineflow\flowItem\argument\ItemArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ConditionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use SOFe\AwaitGenerator\Await;

class HasItemData extends FlowItem implements Condition {
    use ConditionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    private ItemArgument $item;
    private StringArgument $key;

    public function __construct(string $item = "", string $key = "") {
        parent::__construct(self::HAS_ITEM_DATA, FlowItemCategory::ITEM);

        $this->item = new ItemArgument("item", $item);
        $this->key = new StringArgument("key", $key, "@action.setItemData.form.key", example: "aieuo");
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->item->getName(), "key"];
    }

    public function getDetailReplaces(): array {
        return [$this->item->get(), $this->key->get()];
    }

    public function getItem(): ItemArgument {
        return $this->item;
    }

    public function getKey(): StringArgument {
        return $this->key;
    }

    public function isDataValid(): bool {
        return $this->item->isNotEmpty() and $this->key->isNotEmpty();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $item = $this->item->getItem($source);
        $key = $this->key->getString($source);
        $tags = $item->getNamedTag();

        yield Await::ALL;
        return $tags->getTag($key) !== null;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->item->createFormElement($variables),
            $this->key->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->item->set($content[0]);
        $this->key->set($content[1]);
    }

    public function serializeContents(): array {
        return [$this->item->get(), $this->key->get()];
    }
}
