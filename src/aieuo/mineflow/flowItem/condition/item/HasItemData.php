<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\item;

use aieuo\mineflow\flowItem\argument\ItemArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleCondition;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class HasItemData extends SimpleCondition {

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    private ItemArgument $item;
    private StringArgument $key;

    public function __construct(string $item = "", string $key = "") {
        parent::__construct(self::HAS_ITEM_DATA, FlowItemCategory::ITEM);

        $this->setArguments([
            $this->item = new ItemArgument("item", $item),
            $this->key = new StringArgument("key", $key, "@action.setItemData.form.key", example: "aieuo"),
        ]);
    }

    public function getItem(): ItemArgument {
        return $this->item;
    }

    public function getKey(): StringArgument {
        return $this->key;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $item = $this->item->getItem($source);
        $key = $this->key->getString($source);
        $tags = $item->getNamedTag();

        yield Await::ALL;
        return $tags->getTag($key) !== null;
    }
}
