<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\BlockFlowItem;
use aieuo\mineflow\flowItem\base\BlockFlowItemTrait;
use aieuo\mineflow\flowItem\base\PositionFlowItem;
use aieuo\mineflow\flowItem\base\PositionFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\mineflow\CancelToggle;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;

class SetBlock extends FlowItem implements PositionFlowItem, BlockFlowItem {
    use PositionFlowItemTrait, BlockFlowItemTrait;

    protected $id = self::SET_BLOCK;

    protected $name = "action.setBlock.name";
    protected $detail = "action.setBlock.detail";
    protected $detailDefaultReplace = ["position", "block"];

    protected $category = Category::LEVEL;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    public function __construct(string $position = "pos", string $block = "block") {
        $this->setPositionVariableName($position);
        $this->setBlockVariableName($block);
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPositionVariableName(), $this->getBlockVariableName()]);
    }

    public function isDataValid(): bool {
        return $this->getPositionVariableName() !== "" and $this->getBlockVariableName() !== "";
    }

    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $position = $this->getPosition($origin);
        $this->throwIfInvalidPosition($position);

        $block = $this->getBlock($origin);
        $this->throwIfInvalidBlock($block);

        $position->level->setBlock($position, $block);
        yield true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@flowItem.form.target.position", "pos", $default[1] ?? $this->getPositionVariableName(), true),
                new ExampleInput("@flowItem.form.target.block", "block", $default[2] ?? $this->getBlockVariableName(), true),
                new CancelToggle()
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], $data[2]], "cancel" => $data[3], "errors" => []];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setPositionVariableName($content[0]);
        $this->setBlockVariableName($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPositionVariableName(), $this->getBlockVariableName()];
    }
}
