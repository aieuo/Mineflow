<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\scoreboard;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\base\ScoreboardFlowItem;
use aieuo\mineflow\flowItem\base\ScoreboardFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\ScoreboardVariableDropdown;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;

class ShowScoreboard extends FlowItem implements PlayerFlowItem, ScoreboardFlowItem {
    use PlayerFlowItemTrait, ScoreboardFlowItemTrait;

    protected $id = self::SHOW_SCOREBOARD;

    protected $name = "action.showScoreboard.name";
    protected $detail = "action.showScoreboard.detail";
    protected $detailDefaultReplace = ["player", "scoreboard"];

    protected $category = Category::SCOREBOARD;

    public function __construct(string $player = "", string $scoreboard = "") {
        $this->setPlayerVariableName($player);
        $this->setScoreboardVariableName($scoreboard);
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPlayerVariableName(), $this->getScoreboardVariableName()]);
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "" and $this->getScoreboardVariableName() !== "";
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        $board = $this->getScoreboard($source);

        $board->show($player);
        yield true;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
            new ScoreboardVariableDropdown($variables, $this->getScoreboardVariableName()),
        ];
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
