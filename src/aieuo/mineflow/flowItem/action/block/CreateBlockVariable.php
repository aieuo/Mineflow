<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\block;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\EditFormResponseProcessor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
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

    private StringArgument $blockId;
    private StringArgument $variableName;

    public function __construct(string $blockId = "", string $variableName = "block") {
        parent::__construct(self::CREATE_BLOCK_VARIABLE, FlowItemCategory::BLOCK);

        $this->variableName = new StringArgument("block", $variableName, "@action.form.resultVariableName", example: "block");
        $this->blockId = new StringArgument("id", $blockId, example: "1:0");
    }

    public function getDetailDefaultReplaces(): array {
        return ["block", "id"];
    }

    public function getDetailReplaces(): array {
        return [$this->variableName->get(), $this->blockId->get()];
    }

    public function getVariableName(): StringArgument {
        return $this->variableName;
    }

    public function getBlockId(): StringArgument {
        return $this->blockId;
    }

    public function isDataValid(): bool {
        return $this->variableName->isValid() and $this->blockId->isValid();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $this->variableName->getString($source);
        $id = $this->blockId->getString($source);
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
        return $this->variableName->get();
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->blockId->createFormElement($variables),
            $this->variableName->createFormElement($variables),
        ])->response(function (EditFormResponseProcessor $response) {
            $response->rearrange([1, 0]);
        });
    }

    public function loadSaveData(array $content): void {
        $this->variableName->set($content[0]);
        $this->blockId->set($content[1]);
    }

    public function serializeContents(): array {
        return [$this->variableName->get(), $this->blockId->get()];
    }

    public function getAddingVariables(): array {
        return [
            $this->variableName->get() => new DummyVariable(BlockVariable::class, $this->blockId->get())
        ];
    }
}
