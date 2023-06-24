<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\scoreboard;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\placeholder\PlayerPlaceholder;
use aieuo\mineflow\flowItem\placeholder\ScoreboardPlaceholder;
use SOFe\AwaitGenerator\Await;

class ShowScoreboard extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private PlayerPlaceholder $player;
    private ScoreboardPlaceholder $scoreboard;

    public function __construct(string $player = "", string $scoreboard = "") {
        parent::__construct(self::SHOW_SCOREBOARD, FlowItemCategory::SCOREBOARD);

        $this->player = new PlayerPlaceholder("player", $player);
        $this->scoreboard = new ScoreboardPlaceholder("scoreboard", $scoreboard);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->player->getName(), $this->scoreboard->getName()];
    }

    public function getDetailReplaces(): array {
        return [$this->player->get(), $this->scoreboard->get()];
    }

    public function getScoreboard(): ScoreboardPlaceholder {
        return $this->scoreboard;
    }

    public function isDataValid(): bool {
        return $this->player->get() !== "" and $this->scoreboard->isNotEmpty();
    }

    public function getPlayer(): PlayerPlaceholder {
        return $this->player;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $player = $this->player->getOnlinePlayer($source);
        $board = $this->scoreboard->getScoreboard($source);

        $board->show($player);

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
