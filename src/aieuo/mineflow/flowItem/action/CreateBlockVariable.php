<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\BlockObjectVariable;
use pocketmine\item\ItemFactory;

class CreateBlockVariable extends FlowItem {

    protected $id = self::CREATE_BLOCK_VARIABLE;

    protected $name = "action.createBlockVariable.name";
    protected $detail = "action.createBlockVariable.detail";
    protected $detailDefaultReplace = ["block", "id"];

    protected $category = Category::BLOCK;
    protected $returnValueType = self::RETURN_VARIABLE_NAME;

    /** @var string */
    private $variableName;
    /** @var string */
    private $blockId;

    public function __construct(string $id = "", string $name = "block") {
        $this->blockId = $id;
        $this->variableName = $name;
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

    public function execute(Recipe $origin): \Generator {
        $this->throwIfCannotExecute();

        $name = $origin->replaceVariables($this->getVariableName());
        $id = $origin->replaceVariables($this->getBlockId());
        try {
            $item = ItemFactory::fromString($id);
        } catch (\InvalidArgumentException $e) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.createBlockVariable.block.notFound"));
        }

        $block = $item->getBlock();
        if ($item->getId() !== 0 and $block->getId() === 0) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.createBlockVariable.block.notFound"));
        }

        $variable = new BlockObjectVariable($block, $name);
        $origin->addVariable($variable);
        yield true;
        return $this->getVariableName();
    }

    public function getEditForm(array $variables = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@action.createBlockVariable.form.id", "1:0", $this->getBlockId(), true),
                new ExampleInput("@action.form.resultVariableName", "block", $this->getVariableName(), true),
                new CancelToggle()
            ]);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[2], $data[1]], "cancel" => $data[3]];
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
        return [new DummyVariable($this->getVariableName(), DummyVariable::BLOCK, $this->getBlockId())];
    }
}