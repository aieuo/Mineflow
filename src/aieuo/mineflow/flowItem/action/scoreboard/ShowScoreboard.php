<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\scoreboard;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\placeholder\ScoreboardPlaceholder;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use SOFe\AwaitGenerator\Await;

class ShowScoreboard extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private ScoreboardPlaceholder $scoreboard;

    public function __construct(string $player = "", string $scoreboard = "") {
        parent::__construct(self::SHOW_SCOREBOARD, FlowItemCategory::SCOREBOARD);

        $this->setPlayerVariableName($player);
        $this->scoreboard = new ScoreboardPlaceholder("scoreboard", $scoreboard);
    }

    public function getDetailDefaultReplaces(): array {
        return ["player", $this->scoreboard->getName()];
    }

    public function getDetailReplaces(): array {
        return [$this->getPlayerVariableName(), $this->scoreboard->get()];
    }

    public function getScoreboard(): ScoreboardPlaceholder {
        return $this->scoreboard;
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "" and $this->scoreboard->isNotEmpty();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $player = $this->getOnlinePlayer($source);
        $board = $this->scoreboard->getScoreboard($source);

        $board->show($player);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
            $this->scoreboard->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->setPlayerVariableName($content[0]);
        $this->scoreboard->set($content[1]);
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->scoreboard->get()];
    }
}
