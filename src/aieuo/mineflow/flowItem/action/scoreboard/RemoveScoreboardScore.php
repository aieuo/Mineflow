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
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use SOFe\AwaitGenerator\Await;

class RemoveScoreboardScore extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private ScoreboardArgument $scoreboard;

    public function __construct(string $scoreboard = "", private string $scoreName = "") {
        parent::__construct(self::REMOVE_SCOREBOARD_SCORE, FlowItemCategory::SCOREBOARD);

        $this->scoreboard = new ScoreboardArgument("scoreboard", $scoreboard);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->scoreboard->getName(), "name"];
    }

    public function getDetailReplaces(): array {
        return [$this->scoreboard->get(), $this->getScoreName()];
    }

    public function getScoreboard(): ScoreboardArgument {
        return $this->scoreboard;
    }

    public function getScoreName(): string {
        return $this->scoreName;
    }

    public function setScoreName(string $scoreName): void {
        $this->scoreName = $scoreName;
    }

    public function isDataValid(): bool {
        return $this->scoreboard->isNotEmpty();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $source->replaceVariables($this->getScoreName());
        $board = $this->scoreboard->getScoreboard($source);

        $board->removeScore($name);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->scoreboard->createFormElement($variables),
            new ExampleInput("@action.setScore.form.name", "aieuo", $this->getScoreName(), false),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->scoreboard->set($content[0]);
        $this->setScoreName($content[1]);
    }

    public function serializeContents(): array {
        return [$this->scoreboard->get(), $this->getScoreName()];
    }
}
