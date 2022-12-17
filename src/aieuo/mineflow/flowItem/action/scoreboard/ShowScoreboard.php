<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\scoreboard;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\base\ScoreboardFlowItem;
use aieuo\mineflow\flowItem\base\ScoreboardFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\ScoreboardVariableDropdown;
use aieuo\mineflow\utils\Language;
use SOFe\AwaitGenerator\Await;

class ShowScoreboard extends FlowItem implements PlayerFlowItem, ScoreboardFlowItem {
    use PlayerFlowItemTrait, ScoreboardFlowItemTrait;
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    public function __construct(string $player = "", string $scoreboard = "") {
        parent::__construct(self::SHOW_SCOREBOARD, FlowItemCategory::SCOREBOARD);

        $this->setPlayerVariableName($player);
        $this->setScoreboardVariableName($scoreboard);
    }

    public function getDetailDefaultReplaces(): array {
        return ["player", "scoreboard"];
    }

    public function getDetailReplaces(): array {
        return [$this->getPlayerVariableName(), $this->getScoreboardVariableName()];
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "" and $this->getScoreboardVariableName() !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $player = $this->getOnlinePlayer($source);
        $board = $this->getScoreboard($source);

        $board->show($player);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
            new ScoreboardVariableDropdown($variables, $this->getScoreboardVariableName()),
        ]);
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setPlayerVariableName($content[0]);
        $this->setScoreboardVariableName($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->getScoreboardVariableName()];
    }
}
