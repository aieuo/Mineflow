<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\BlockObjectVariable;
use pocketmine\item\ItemFactory;

class CreateBlockVariable extends Action {

    protected $id = self::CREATE_BLOCK_VARIABLE;

    protected $name = "action.createBlockVariable.name";
    protected $detail = "action.createBlockVariable.detail";
    protected $detailDefaultReplace = ["block", "id"];

    protected $category = Category::BLOCK;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;
    protected $returnValueType = self::RETURN_VARIABLE_NAME;

    /** @var string */
    private $variableName;
    /** @var string */
    private $blockId;

    public function __construct(string $id = "", string $name = "block") {
        $this->blockId = $id;
        $this->variableName = $name;
    }

    public function setVariableName(string $variableName) {
        $this->variableName = $variableName;
    }

    public function getVariableName(): string {
        return $this->variableName;
    }

    public function setBlockId(string $id) {
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

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $name = $origin->replaceVariables($this->getVariableName());
        $id = $origin->replaceVariables($this->getBlockId());
        try {
            $item = ItemFactory::fromString($id);
        } catch (\InvalidArgumentException $e) {
            throw new \UnexpectedValueException(Language::get("flowItem.error", [$this->getName(), ["action.createBlockVariable.block.notFound"]]));
        }

        $block = $item->getBlock();
        if ($item->getId() !== 0 and $block->getId() === 0) {
            throw new \UnexpectedValueException(Language::get("flowItem.error", [$this->getName(), ["action.createBlockVariable.block.notFound"]]));
        }

        $variable = new BlockObjectVariable($block, $name);
        $origin->addVariable($variable);
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@action.createBlockVariable.form.id", Language::get("form.example", ["1:0"]), $default[1] ?? $this->getBlockId()),
                new Input("@flowItem.form.resultVariableName", Language::get("form.example", ["block"]), $default[2] ?? $this->getVariableName()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[1] === "") {
            $errors[] = ["@form.insufficient", 1];
        }
        if ($data[2] === "") $data[2] = "block";
        return ["contents" => [$data[2], $data[1]], "cancel" => $data[3], "errors" => $errors];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[1])) throw new \OutOfBoundsException();
        $this->setVariableName($content[0]);
        $this->setBlockId($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getVariableName(), $this->getBlockId()];
    }

    public function getReturnValue(): string {
        return $this->getVariableName();
    }
}