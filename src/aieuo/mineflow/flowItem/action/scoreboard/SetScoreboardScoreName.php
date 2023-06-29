<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\scoreboard;

use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\ScoreboardArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use SOFe\AwaitGenerator\Await;

class SetScoreboardScoreName extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private ScoreboardArgument $scoreboard;
    private StringArgument $scoreName;
    private NumberArgument $score;

    public function __construct(string $scoreboard = "", string $scoreName = "", string $score = "") {
        parent::__construct(self::SET_SCOREBOARD_SCORE_NAME, FlowItemCategory::SCOREBOARD);

        $this->scoreboard = new ScoreboardArgument("scoreboard", $scoreboard);
        $this->scoreName = new StringArgument("name", $scoreName, "@action.setScore.form.name", example: "aieuo");
        $this->score = new NumberArgument("score", $score, "@action.setScore.form.score", example: "100");
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->scoreboard->getName(), "name", "score"];
    }

    public function getDetailReplaces(): array {
        return [$this->scoreboard->get(), $this->scoreName->get(), $this->score->get()];
    }

    public function getScoreboard(): ScoreboardArgument {
        return $this->scoreboard;
    }

    public function getScoreName(): StringArgument {
        return $this->scoreName;
    }

    public function getScore(): NumberArgument {
        return $this->score;
    }

    public function isDataValid(): bool {
        return $this->scoreboard->isValid() and $this->score->isValid();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $this->scoreName->getString($source);
        $score = $this->score->getInt($source);
        $board = $this->scoreboard->getScoreboard($source);

        $board->setScoreName($name, $score);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->scoreboard->createFormElement($variables),
            $this->scoreName->createFormElement($variables),
            $this->score->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->scoreboard->set($content[0]);
        $this->scoreName->set($content[1]);
        $this->score->set($content[2]);
    }

    public function serializeContents(): array {
        return [$this->scoreboard->get(), $this->scoreName->get(), $this->score->get()];
    }
}
