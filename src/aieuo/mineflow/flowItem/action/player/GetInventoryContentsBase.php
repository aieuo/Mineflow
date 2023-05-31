<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\object\ItemVariable;

abstract class GetInventoryContentsBase extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    public function __construct(
        string         $id,
        string         $category = FlowItemCategory::PLAYER,
        string         $player = "",
        private string $resultName = "inventory",
    ) {
        parent::__construct($id, $category);

        $this->setPlayerVariableName($player);
    }

    public function getDetailDefaultReplaces(): array {
        return ["player", "inventory"];
    }

    public function getDetailReplaces(): array {
        return [$this->getPlayerVariableName(), $this->getResultName()];
    }

    public function setResultName(string $health): void {
        $this->resultName = $health;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "" and $this->resultName !== "";
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
            new ExampleInput("@action.form.resultVariableName", "inventory", $this->getResultName(), true),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->setPlayerVariableName($content[0]);
        $this->setResultName($content[1]);
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->getResultName()];
    }

    public function getAddingVariables(): array {
        return [
            $this->getResultName() => new DummyVariable(ListVariable::class, ItemVariable::getTypeName())
        ];
    }
}
