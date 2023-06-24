<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\ScoreboardArgument;
use SOFe\AwaitGenerator\Await;

class HideScoreboard extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private PlayerArgument $player;
    private ScoreboardArgument $scoreboard;

    public function __construct(string $player = "", string $scoreboard = "") {
        parent::__construct(self::HIDE_SCOREBOARD, FlowItemCategory::SCOREBOARD);

        $this->player = new PlayerArgument("player", $player);
        $this->scoreboard = new ScoreboardArgument("scoreboard", $scoreboard);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->player->getName(), $this->scoreboard->getName()];
    }

    public function getDetailReplaces(): array {
        return [$this->player->get(), $this->scoreboard->get()];
    }

    public function getScoreboard(): ScoreboardArgument {
        return $this->scoreboard;
    }

    public function isDataValid(): bool {
        return $this->player->get() !== "" and $this->scoreboard->isNotEmpty();
    }

    public function getPlayer(): PlayerArgument {
        return $this->player;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $player = $this->player->getOnlinePlayer($source);
        $board = $this->scoreboard->getScoreboard($source);

        $board->hide($player);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->player->createFormElement($variables),
            $this->scoreboard->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->player->set($content[0]);
        $this->scoreboard->set($content[1]);
    }

    public function serializeContents(): array {
        return [$this->player->get(), $this->scoreboard->get()];
    }
}
