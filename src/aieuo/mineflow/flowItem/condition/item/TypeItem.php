<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\item;

use aieuo\mineflow\flowItem\base\ConditionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\placeholder\ItemPlaceholder;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;

abstract class TypeItem extends FlowItem implements Condition, PlayerFlowItem {
    use PlayerFlowItemTrait;
    use ConditionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected ItemPlaceholder $item;

    public function __construct(
        string $id,
        string $category = FlowItemCategory::INVENTORY,
        string $player = "",
        string $item = "",
    ) {
        parent::__construct($id, $category);

        $this->setPlayerVariableName($player);
        $this->item = new ItemPlaceholder("item", $item);
    }

    public function getDetailDefaultReplaces(): array {
        return ["player", $this->item->getName()];
    }

    public function getDetailReplaces(): array {
        return [$this->getPlayerVariableName(), $this->item->get()];
    }

    public function getItem(): ItemPlaceholder {
        return $this->item;
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "" and $this->item->isNotEmpty();
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
            $this->item->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->setPlayerVariableName($content[0]);
        $this->item->set($content[1]);
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->item->get()];
    }
}
