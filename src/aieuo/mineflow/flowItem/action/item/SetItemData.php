<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\item;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\ItemArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\Main;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\Variable;
use pocketmine\nbt\NbtException;
use SOFe\AwaitGenerator\Await;

class SetItemData extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    public function __construct(string $item = "", string $key = "", string $value = "") {
        parent::__construct(self::SET_ITEM_DATA, FlowItemCategory::ITEM);

        $this->setArguments([
            ItemArgument::create("item", $item),
            StringArgument::create("key", $key)->example("aieuo"),
            StringArgument::create("value", $value)->example("100"),
        ]);
    }

    public function getItem(): ItemArgument {
        return $this->getArgument("item");
    }

    public function getKey(): StringArgument {
        return $this->getArgument("key");
    }

    public function getValue(): StringArgument {
        return $this->getArgument("value");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $item = $this->getItem()->getItem($source);
        $key = $this->getKey()->getString($source);
        $variable = $this->getValueVariable($source);

        $tags = $item->getNamedTag();
        try {
            $tags->setTag($key, $variable->toNBTTag());
            $item->setNamedTag($tags);
        } catch (\UnexpectedValueException|NbtException $e) {
            if (Mineflow::isDebug()) Main::getInstance()->getLogger()->logException($e);
            throw new InvalidFlowValueException(Language::get("variable.convert.nbt.failed", [$e->getMessage(), (string)$variable]));
        }

        yield Await::ALL;
        return (string)$this->getItem();
    }

    public function getValueVariable(FlowItemExecutor $source): Variable {
        $helper = Mineflow::getVariableHelper();
        $value = $this->getValue()->getRawString();

        return $helper->copyOrCreateVariable($value, $source->getVariableRegistryCopy());
    }
}