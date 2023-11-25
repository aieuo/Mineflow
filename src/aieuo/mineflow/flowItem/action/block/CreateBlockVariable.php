<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\block;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\page\custom\CustomFormResponseProcessor;
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

class CreateBlockVariable extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    public function __construct(string $blockId = "", string $variableName = "block") {
        parent::__construct(self::CREATE_BLOCK_VARIABLE, FlowItemCategory::BLOCK);

        $this->setArguments([
            new StringArgument("block", $variableName, "@action.form.resultVariableName", example: "block"),
            new StringArgument("id", $blockId, example: "1:0"),
        ]);
    }

    public function getVariableName(): StringArgument {
        return $this->getArguments()[0];
    }

    public function getBlockId(): StringArgument {
        return $this->getArguments()[1];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $this->getVariableName()->getString($source);
        $id = $this->getBlockId()->getString($source);
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
        return (string)$this->getVariableName();
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->getBlockId()->createFormElements($variables)[0],
            $this->getVariableName()->createFormElements($variables)[0],
        ])->response(function (CustomFormResponseProcessor $response) {
            $response->rearrange([1, 0]);
        });
    }

    public function getAddingVariables(): array {
        return [
            (string)$this->getVariableName() => new DummyVariable(BlockVariable::class, (string)$this->getBlockId())
        ];
    }
}
