<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\item;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\ItemArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\utils\Language;
use SOFe\AwaitGenerator\Await;

class GetItemData extends SimpleAction {

    protected string $id = self::GET_ITEM_DATA;

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;
    private ItemArgument $item;
    private StringArgument $key;
    private StringArgument $resultName;

    public function __construct(string         $item = "", string $key = "", string $resultName = "data") {
        parent::__construct(self::GET_ITEM_DATA, FlowItemCategory::ITEM);

        $this->setArguments([
            $this->item = new ItemArgument("item", $item),
            $this->key = new StringArgument("key", $key, "@action.setItemData.form.key", example: "aieuo"),
            $this->resultName = new StringArgument("result", $resultName, "@action.form.resultVariableName", example: "entity"),
        ]);
    }

    public function getItem(): ItemArgument {
        return $this->item;
    }

    public function getKey(): StringArgument {
        return $this->key;
    }

    public function getResultName(): StringArgument {
        return $this->resultName;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $item = $this->item->getItem($source);
        $key = $this->key->getString($source);
        $resultName = $this->resultName->getString($source);

        $tags = $item->getNamedTag();
        $tag = $tags->getTag($key);
        if ($tag === null) {
            throw new InvalidFlowValueException(Language::get("action.getItemData.tag.not.exists", [$key]));
        }

        $variable = Mineflow::getVariableHelper()->tagToVariable($tag);
        $source->addVariable($resultName, $variable);

        yield Await::ALL;
        return $this->item->get();
    }
}
