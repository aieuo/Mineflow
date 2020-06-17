<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\BlockFlowItem;
use aieuo\mineflow\flowItem\base\BlockFlowItemTrait;
use aieuo\mineflow\flowItem\base\PositionFlowItem;
use aieuo\mineflow\flowItem\base\PositionFlowItemTrait;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;

class SetBlock extends Action implements PositionFlowItem, BlockFlowItem {
    use PositionFlowItemTrait, BlockFlowItemTrait;

    protected $id = self::SET_BLOCK;

    protected $name = "action.setBlock.name";
    protected $detail = "action.setBlock.detail";
    protected $detailDefaultReplace = ["position", "block"];

    protected $category = Category::LEVEL;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    public function __construct(string $position = "pos", string $block = "block") {
        $this->positionVariableName = $position;
        $this->blockVariableName = $block;
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPositionVariableName(), $this->getBlockVariableName()]);
    }

    public function isDataValid(): bool {
        return $this->getPositionVariableName() !== "" and $this->getBlockVariableName() !== "";
    }

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $position = $this->getPosition($origin);
        $this->throwIfInvalidPosition($position);

        $block = $this->getBlock($origin);
        $this->throwIfInvalidBlock($block);

        $position->level->setBlock($position, $block);
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@flowItem.form.target.position", Language::get("form.example", ["pos"]), $default[1] ?? $this->getPositionVariableName()),
                new Input("@flowItem.form.target.block", Language::get("form.example", ["block"]), $default[2] ?? $this->getBlockVariableName()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        if ($data[1] === "") $data[1] = "pos";
        if ($data[2] === "") $data[2] = "block";
        return ["contents" => [$data[1], $data[2]], "cancel" => $data[3], "errors" => []];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[1])) throw new \OutOfBoundsException();
        $this->setPositionVariableName($content[0]);
        $this->setBlockVariableName($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPositionVariableName(), $this->getBlockVariableName()];
    }
}
