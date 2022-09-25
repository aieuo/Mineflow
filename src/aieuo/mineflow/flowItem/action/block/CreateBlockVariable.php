<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\block;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\BlockVariable;
use pocketmine\item\LegacyStringToItemParser;
use pocketmine\item\StringToItemParser;

class CreateBlockVariable extends FlowItem {

    protected string $id = self::CREATE_BLOCK_VARIABLE;

    protected string $name = "action.createBlockVariable.name";
    protected string $detail = "action.createBlockVariable.detail";
    protected array $detailDefaultReplace = ["block", "id"];

    protected string $category = FlowItemCategory::BLOCK;
    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    public function __construct(
        private string $blockId = "",
        private string $variableName = "block"
    ) {
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

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getVariableName(), $this->getBlockId()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $name = $source->replaceVariables($this->getVariableName());
        $id = $source->replaceVariables($this->getBlockId());
        try {
            $item = StringToItemParser::getInstance()->getInstance()->parse($id) ?? LegacyStringToItemParser::getInstance()->parse($id);
        } catch (\InvalidArgumentException) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.createBlockVariable.block.notFound"));
        }

        $block = $item->getBlock();
        if ($item->getId() !== 0 and $block->getId() === 0) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.createBlockVariable.block.notFound"));
        }

        $variable = new BlockVariable($block, $name);
        $source->addVariable($name, $variable);
        yield true;
        return $this->getVariableName();
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.createBlockVariable.form.id", "1:0", $this->getBlockId(), true),
            new ExampleInput("@action.form.resultVariableName", "block", $this->getVariableName(), true),
        ];
    }

    public function parseFromFormData(array $data): array {
        return [$data[1], $data[0]];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setVariableName($content[0]);
        $this->setBlockId($content[1]);
        return $this;
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
