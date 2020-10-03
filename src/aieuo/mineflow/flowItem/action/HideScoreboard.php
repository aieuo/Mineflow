<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\base\ScoreboardFlowItem;
use aieuo\mineflow\flowItem\base\ScoreboardFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\mineflow\CancelToggle;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\ScoreboardVariableDropdown;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;

class HideScoreboard extends FlowItem implements PlayerFlowItem, ScoreboardFlowItem {
    use PlayerFlowItemTrait, ScoreboardFlowItemTrait;

    protected $id = self::HIDE_SCOREBOARD;

    protected $name = "action.hideScoreboard.name";
    protected $detail = "action.hideScoreboard.detail";
    protected $detailDefaultReplace = ["player", "scoreboard"];

    protected $category = Category::PLAYER;

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

    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $player = $this->getPlayer($origin);
        $this->throwIfInvalidPlayer($player);

        $board = $this->getScoreboard($origin);
        $this->throwIfInvalidScoreboard($board);

        $board->hide($player);
        yield true;
    }

    public function getEditForm(array $variables = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
                new ScoreboardVariableDropdown($variables, $this->getScoreboardVariableName()),
                new CancelToggle()
            ]);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], $data[2]], "cancel" => $data[3]];
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
