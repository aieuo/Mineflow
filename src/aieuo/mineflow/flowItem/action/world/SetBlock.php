<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\BlockFlowItem;
use aieuo\mineflow\flowItem\base\BlockFlowItemTrait;
use aieuo\mineflow\flowItem\base\PositionFlowItem;
use aieuo\mineflow\flowItem\base\PositionFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\BlockVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\PositionVariableDropdown;
use aieuo\mineflow\utils\Language;
use SOFe\AwaitGenerator\Await;

class SetBlock extends FlowItem implements PositionFlowItem, BlockFlowItem {
    use PositionFlowItemTrait, BlockFlowItemTrait;
    use ActionNameWithMineflowLanguage;

    public function __construct(string $position = "", string $block = "") {
        parent::__construct(self::SET_BLOCK, FlowItemCategory::WORLD);

        $this->setPositionVariableName($position);
        $this->setBlockVariableName($block);
    }

    public function getDetailDefaultReplaces(): array {
        return ["position", "block"];
    }

    public function getDetailReplaces(): array {
        return [$this->getPositionVariableName(), $this->getBlockVariableName()];
    }

    public function isDataValid(): bool {
        return $this->getPositionVariableName() !== "" and $this->getBlockVariableName() !== "";
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $position = $this->getPosition($source);

        $block = $this->getBlock($source);

        $position->world->setBlock($position, $block);

        yield Await::ALL;
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
