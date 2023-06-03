<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\player;

use aieuo\mineflow\flowItem\base\ConditionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use SOFe\AwaitGenerator\Await;

class IsPlayerOnline extends FlowItem implements Condition, PlayerFlowItem {
    use PlayerFlowItemTrait;
    use ConditionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    public function __construct(string $player = "") {
        parent::__construct(self::IS_PLAYER_ONLINE, FlowItemCategory::PLAYER);

        $this->setPlayerVariableName($player);
    }

    public function getDetailDefaultReplaces(): array {
        return ["player"];
    }

    public function getDetailReplaces(): array {
        return [$this->getPlayerVariableName()];
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== null;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $player = $this->getPlayer($source);

        yield Await::ALL;
        return $player->isOnline();
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->setPlayerVariableName($content[0]);
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName()];
    }
}
