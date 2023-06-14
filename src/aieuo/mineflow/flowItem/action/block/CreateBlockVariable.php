<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\block;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\EditFormResponseProcessor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\BlockVariable;
use pocketmine\block\BlockTypeIds;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\LegacyStringToItemParser;
use pocketmine\item\LegacyStringToItemParserException;
use pocketmine\item\StringToItemParser;
use SOFe\AwaitGenerator\Await;

class CreateBlockVariable extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    public function __construct(
        private string $blockId = "",
        private string $variableName = "block"
    ) {
        parent::__construct(self::CREATE_BLOCK_VARIABLE, FlowItemCategory::BLOCK);
    }

    public function getDetailDefaultReplaces(): array {
        return ["block", "id"];
    }

    public function getDetailReplaces(): array {
        return [$this->getVariableName(), $this->getBlockId()];
    }

    public function setVariableName(string $variableName): void {
        $this->variableName = $variableName;
    }

    public function getVariableName(): string {
        return $this->variableName;
    }

    public function setBlockId(string $id): void {
        $this->blockId = $id;
    }

    public function getBlockId(): string {
        return $this->blockId;
    }

    public function isDataValid(): bool {
        return $this->variableName !== "" and $this->blockId !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $source->replaceVariables($this->getVariableName());
        $id = $source->replaceVariables($this->getBlockId());
        try {
            $item = StringToItemParser::getInstance()->getInstance()->parse($id) ?? LegacyStringToItemParser::getInstance()->parse($id);
        } catch (\InvalidArgumentException|LegacyStringToItemParserException) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.createBlock.block.notFound"));
        }

        $block = $item->getBlock();
        if ($item->getTypeId() !== ItemTypeIds::fromBlockTypeId(BlockTypeIds::AIR) and $block->getTypeId() === BlockTypeIds::AIR) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.createBlock.block.notFound"));
        }

        $variable = new BlockVariable($block);
        $source->addVariable($name, $variable);

        yield Await::ALL;
        return $this->getVariableName();
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new ExampleInput("@action.createBlock.form.id", "1:0", $this->getBlockId(), true),
            new ExampleInput("@action.form.resultVariableName", "block", $this->getVariableName(), true),
        ])->response(function (EditFormResponseProcessor $response) {
            $response->rearrange([1, 0]);
        });
    }

    public function loadSaveData(array $content): void {
        $this->setVariableName($content[0]);
        $this->setBlockId($content[1]);
    }

    public function serializeContents(): array {
        return [$this->getVariableName(), $this->getBlockId()];
    }

    public function getAddingVariables(): array {
        return [
            $this->getVariableName() => new DummyVariable(BlockVariable::class, $this->getBlockId())
        ];
    }
}
