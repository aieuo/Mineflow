<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\scoreboard;

use aieuo\mineflow\flowItem\argument\ScoreboardArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use SOFe\AwaitGenerator\Await;

class RemoveScoreboardScore extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private ScoreboardArgument $scoreboard;
    private StringArgument $scoreName;

    public function __construct(string $scoreboard = "", string $scoreName = "") {
        parent::__construct(self::REMOVE_SCOREBOARD_SCORE, FlowItemCategory::SCOREBOARD);

        $this->scoreboard = new ScoreboardArgument("scoreboard", $scoreboard);
        $this->scoreName = new StringArgument("name", $scoreName, "@action.setScore.form.name", example: "aieuo", optional: true);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->scoreboard->getName(), "name"];
    }

    public function getDetailReplaces(): array {
        return [$this->scoreboard->get(), $this->scoreName->get()];
    }

    public function getScoreboard(): ScoreboardArgument {
        return $this->scoreboard;
    }

    public function getScoreName(): StringArgument {
        return $this->scoreName;
    }

    public function isDataValid(): bool {
        return $this->scoreboard->isValid();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $this->scoreName->getString($source);
        $board = $this->scoreboard->getScoreboard($source);

        $board->removeScore($name);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->scoreboard->createFormElement($variables),
            $this->scoreName->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->scoreboard->set($content[0]);
        $this->scoreName->set($content[1]);
    }

    public function serializeContents(): array {
        return [$this->scoreboard->get(), $this->scoreName->get()];
    }
}
