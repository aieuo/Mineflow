<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player\message;

use aieuo\mineflow\exception\InvalidFormValueException;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\EditFormResponseProcessor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\placeholder\PlayerPlaceholder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use SOFe\AwaitGenerator\Await;

class SendTitle extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private PlayerPlaceholder $player;

    public function __construct(
        string         $player = "",
        private string $title = "",
        private string $subtitle = "",
        private string $fadein = "-1",
        private string $stay = "-1",
        private string $fadeout = "-1"
    ) {
        parent::__construct(self::SEND_TITLE, FlowItemCategory::PLAYER_MESSAGE);

        $this->player = new PlayerPlaceholder("player", $player);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->player->getName(), "title", "subtitle", "fadein", "stay", "fadeout"];
    }

    public function getDetailReplaces(): array {
        return [$this->player->get(), $this->getTitle(), $this->getSubTitle(), $this->fadein, $this->stay, $this->fadeout];
    }

    public function setTitle(string $title, string $subtitle = ""): void {
        $this->title = $title;
        $this->subtitle = $subtitle;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function getSubTitle(): string {
        return $this->subtitle;
    }

    public function setTime(string $fadeIn = "-1", string $stay = "-1", string $fadeOut = "-1"): void {
        $this->fadein = $fadeIn;
        $this->stay = $stay;
        $this->fadeout = $fadeOut;
    }

    public function getTime(): array {
        return [$this->fadein, $this->stay, $this->fadeout];
    }

    public function isDataValid(): bool {
        return $this->player->get() !== "" and ($this->getTitle() !== "" or $this->getSubTitle() !== "");
    }

    public function getPlayer(): PlayerPlaceholder {
        return $this->player;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $title = $source->replaceVariables($this->getTitle());
        $subtitle = $source->replaceVariables($this->getSubTitle());
        $times = array_map(fn($time) => $this->getInt($source->replaceVariables($time)), $this->getTime());
        $player = $this->player->getOnlinePlayer($source);

        $player->sendTitle($title, $subtitle, ...$times);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->player->createFormElement($variables),
            new ExampleInput("@action.sendTitle.form.title", "aieuo", $this->getTitle()),
            new ExampleInput("@action.sendTitle.form.subtitle", "aieuo", $this->getSubTitle()),
            new ExampleNumberInput("@action.sendTitle.form.fadein", "-1", $this->fadein, true, -1),
            new ExampleNumberInput("@action.sendTitle.form.stay", "-1", $this->stay, true, -1),
            new ExampleNumberInput("@action.sendTitle.form.fadeout", "-1", $this->fadeout, true, -1),
        ])->response(function (EditFormResponseProcessor $response) {
            $response->validate(function (array $data) {
                if ($data[1] === "" and $data[2] === "") {
                    throw new InvalidFormValueException("@form.insufficient", 1);
                }
            });
        });
    }

    public function loadSaveData(array $content): void {
        $this->player->set($content[0]);
        $this->setTitle($content[1], $content[2]);
        if (isset($content[5])) {
            $this->setTime($content[3], $content[4], $content[5]);
        }
    }

    public function serializeContents(): array {
        return array_merge([$this->player->get(), $this->getTitle(), $this->getSubTitle()], $this->getTime());
    }
}
