<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\item;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\placeholder\ItemPlaceholder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\Main;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\Variable;
use pocketmine\nbt\NbtException;
use SOFe\AwaitGenerator\Await;

class SetItemData extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;
    private ItemPlaceholder $item;

    public function __construct(
        string         $item = "",
        private string $key = "",
        private string $value = "",
    ) {
        parent::__construct(self::SET_ITEM_DATA, FlowItemCategory::ITEM);

        $this->item = new ItemPlaceholder("item", $item);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->item->getName(), "key", "value"];
    }

    public function getDetailReplaces(): array {
        return [$this->item->get(), $this->getKey(), $this->getValue()];
    }

    public function getItem(): ItemPlaceholder {
        return $this->item;
    }

    public function setKey(string $key): void {
        $this->key = $key;
    }

    public function getKey(): string {
        return $this->key;
    }

    public function setValue(string $value): void {
        $this->value = $value;
    }

    public function getValue(): string {
        return $this->value;
    }

    public function isDataValid(): bool {
        return $this->item->isNotEmpty() and $this->getKey() !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $item = $this->item->getItem($source);
        $key = $source->replaceVariables($this->getKey());
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
        return $this->item->get();
    }

    public function getValueVariable(FlowItemExecutor $source): Variable {
        $helper = Mineflow::getVariableHelper();
        $value = $this->getValue();

        return $helper->copyOrCreateVariable($value, $source);
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->item->createFormElement($variables),
            new ExampleInput("@action.setItemData.form.key", "aieuo", $this->getKey(), true),
            new ExampleInput("@action.setItemData.form.value", "100", $this->getValue(), true),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->item->set($content[0]);
        $this->setKey($content[1]);
        $this->setValue($content[2]);
    }

    public function serializeContents(): array {
        return [$this->item->get(), $this->getKey(), $this->getValue()];
    }
}
