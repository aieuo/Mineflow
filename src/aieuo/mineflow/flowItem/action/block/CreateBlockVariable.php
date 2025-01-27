<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\block;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\editor\MainFlowItemEditor;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
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
            StringArgument::create("block", $variableName, "@action.form.resultVariableName")->example("block"),
            StringArgument::create("id", $blockId)->example("stone"),
        ]);
    }

    public function getVariableName(): StringArgument {
        return $this->getArgument("block");
    }

    public function getBlockId(): StringArgument {
        return $this->getArgument("id");
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

    public function getAddingVariables(): array {
        return [
            (string)$this->getVariableName() => new DummyVariable(BlockVariable::class, (string)$this->getBlockId())
        ];
    }

    public function getEditors(): array {
        return [
            new MainFlowItemEditor($this, [
                $this->getBlockId(),
                $this->getVariableName(),
            ]),
        ];
    }
}