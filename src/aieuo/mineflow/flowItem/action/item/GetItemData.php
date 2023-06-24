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
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\utils\Language;
use SOFe\AwaitGenerator\Await;

class GetItemData extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected string $id = self::GET_ITEM_DATA;

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;
    private ItemPlaceholder $item;

    public function __construct(
        string         $item = "",
        private string $key = "",
        private string $resultName = "data",
    ) {
        parent::__construct(self::GET_ITEM_DATA, FlowItemCategory::ITEM);

        $this->item = new ItemPlaceholder("item", $item);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->item->getName(), "key"];
    }

    public function getDetailReplaces(): array {
        return [$this->item->get(), $this->getKey()];
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

    public function setResultName(string $resultName): void {
        $this->resultName = $resultName;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->item->isNotEmpty() and $this->getKey() !== "" and $this->getResultName() !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $item = $this->item->getItem($source);
        $key = $source->replaceVariables($this->getKey());
        $resultName = $source->replaceVariables($this->getResultName());

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

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->item->createFormElement($variables),
            new ExampleInput("@action.setItemData.form.key", "aieuo", $this->getKey(), true),
            new ExampleInput("@action.form.resultVariableName", "entity", $this->getResultName(), true),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->item->set($content[0]);
        $this->setKey($content[1]);
        $this->setResultName($content[2]);
    }

    public function serializeContents(): array {
        return [$this->item->get(), $this->getKey(), $this->getResultName()];
    }
}
