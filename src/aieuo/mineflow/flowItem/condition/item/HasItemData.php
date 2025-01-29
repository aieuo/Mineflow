<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\item;

use aieuo\mineflow\flowItem\argument\ItemArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleCondition;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\libs\_30a18b127a564f2c\SOFe\AwaitGenerator\Await;

class HasItemData extends SimpleCondition {

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    public function __construct(string $item = "", string $key = "") {
        parent::__construct(self::HAS_ITEM_DATA, FlowItemCategory::ITEM);

        $this->setArguments([
            ItemArgument::create("item", $item),
            StringArgument::create("key", $key, "@action.setItemData.form.key")->example("aieuo"),
        ]);
    }

    public function getItem(): ItemArgument {
        return $this->getArgument("item");
    }

    public function getKey(): StringArgument {
        return $this->getArgument("key");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $item = $this->getItem()->getItem($source);
        $key = $this->getKey()->getString($source);
        $tags = $item->getNamedTag();

        yield Await::ALL;
        return $tags->getTag($key) !== null;
    }
}