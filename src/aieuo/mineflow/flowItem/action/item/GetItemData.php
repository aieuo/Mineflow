<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\item;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\ItemFlowItem;
use aieuo\mineflow\flowItem\base\ItemFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\ItemVariableDropdown;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\utils\Language;
use SOFe\AwaitGenerator\Await;

class GetItemData extends FlowItem implements ItemFlowItem {
    use ItemFlowItemTrait;
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected string $id = self::GET_ITEM_DATA;

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    public function __construct(
        string         $item = "",
        private string $key = "",
        private string $resultName = "data",
    ) {
        parent::__construct(self::GET_ITEM_DATA, FlowItemCategory::ITEM);

        $this->setItemVariableName($item);
    }

    public function getDetailDefaultReplaces(): array {
        return ["item", "key"];
    }

    public function getDetailReplaces(): array {
        return [$this->getItemVariableName(), $this->getKey()];
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
        return $this->getItemVariableName() !== "" and $this->getKey() !== "" and $this->getResultName() !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $item = $this->getItem($source);
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
        return $this->getItemVariableName();
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new ItemVariableDropdown($variables, $this->getItemVariableName()),
            new ExampleInput("@action.setItemData.form.key", "aieuo", $this->getKey(), true),
            new ExampleInput("@action.form.resultVariableName", "entity", $this->getResultName(), true),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->setItemVariableName($content[0]);
        $this->setKey($content[1]);
        $this->setResultName($content[2]);
    }

    public function serializeContents(): array {
        return [$this->getItemVariableName(), $this->getKey(), $this->getResultName()];
    }
}
