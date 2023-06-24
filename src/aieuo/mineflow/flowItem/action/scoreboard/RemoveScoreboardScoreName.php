<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\scoreboard;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\argument\ScoreboardArgument;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use SOFe\AwaitGenerator\Await;

class RemoveScoreboardScoreName extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private ScoreboardArgument $scoreboard;

    public function __construct(string $scoreboard = "", private string $score = "") {
        parent::__construct(self::REMOVE_SCOREBOARD_SCORE_NAME, FlowItemCategory::SCOREBOARD);

        $this->scoreboard = new ScoreboardArgument("scoreboard", $scoreboard);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->scoreboard->getName(), "score"];
    }

    public function getDetailReplaces(): array {
        return [$this->scoreboard->get(), $this->getScore()];
    }

    public function getScoreboard(): ScoreboardArgument {
        return $this->scoreboard;
    }

    public function getScore(): string {
        return $this->score;
    }

    public function setScore(string $score): void {
        $this->score = $score;
    }

    public function isDataValid(): bool {
        return $this->scoreboard->isNotEmpty() and $this->getScore() !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $score = $this->getInt($source->replaceVariables($this->getScore()));
        $board = $this->scoreboard->getScoreboard($source);

        $board->removeScoreName($score);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->scoreboard->createFormElement($variables),
            new ExampleNumberInput("@action.setScore.form.score", "100", $this->getScore(), true),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->scoreboard->set($content[0]);
        $this->setScore($content[1]);
    }

    public function serializeContents(): array {
        return [$this->scoreboard->get(), $this->getScore()];
    }
}
