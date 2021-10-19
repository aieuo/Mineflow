<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\base\BlockFlowItem;
use aieuo\mineflow\flowItem\base\BlockFlowItemTrait;
use aieuo\mineflow\flowItem\base\PositionFlowItem;
use aieuo\mineflow\flowItem\base\PositionFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\BlockVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\PositionVariableDropdown;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;

class SetBlock extends FlowItem implements PositionFlowItem, BlockFlowItem {
    use PositionFlowItemTrait, BlockFlowItemTrait;

    protected string $id = self::SET_BLOCK;

    protected string $name = "action.setBlock.name";
    protected string $detail = "action.setBlock.detail";
    protected array $detailDefaultReplace = ["position", "block"];

    protected string $category = Category::WORLD;

    public function __construct(string $position = "", string $block = "") {
        $this->setPositionVariableName($position);
        $this->setBlockVariableName($block);
    }

    public function getDetail(): string {
        return Language::get($this->detail, [$this->getPositionVariableName(), $this->getBlockVariableName()]);
    }

    public function isDataValid(): bool {
        return $this->getPositionVariableName() !== "" and $this->getBlockVariableName() !== "";
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $position = $this->getPosition($source);

        $block = $this->getBlock($source);

        $position->level->setBlock($position, $block);
        yield FlowItemExecutor::CONTINUE;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new PositionVariableDropdown($variables, $this->getPositionVariableName()),
            new BlockVariableDropdown($variables, $this->getBlockVariableName()),
        ];
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
