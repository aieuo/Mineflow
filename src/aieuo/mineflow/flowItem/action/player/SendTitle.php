<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\exception\InvalidFormValueException;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\utils\Language;
use SOFe\AwaitGenerator\Await;

class SendTitle extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;
    use ActionNameWithMineflowLanguage;

    public function __construct(
        string         $player = "",
        private string $title = "",
        private string $subtitle = "",
        private string $fadein = "-1",
        private string $stay = "-1",
        private string $fadeout = "-1"
    ) {
        parent::__construct(self::SEND_TITLE, FlowItemCategory::PLAYER);

        $this->setPlayerVariableName($player);
    }

    public function getDetailDefaultReplaces(): array {
        return ["player", "title", "subtitle", "fadein", "stay", "fadeout"];
    }

    public function getDetailReplaces(): array {
        return [$this->getPlayerVariableName(), $this->getTitle(), $this->getSubTitle(), $this->fadein, $this->stay, $this->fadeout];
    }

    public function setTitle(string $title, string $subtitle = ""): self {
        $this->title = $title;
        $this->subtitle = $subtitle;
        return $this;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function getSubTitle(): string {
        return $this->subtitle;
    }

    public function setTime(string $fadeIn = "-1", string $stay = "-1", string $fadeOut = "-1"): self {
        $this->fadein = $fadeIn;
        $this->stay = $stay;
        $this->fadeout = $fadeOut;
        return $this;
    }

    public function getTime(): array {
        return [$this->fadein, $this->stay, $this->fadeout];
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "" and ($this->getTitle() !== "" or $this->getSubTitle() !== "");
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $title = $source->replaceVariables($this->getTitle());
        $subtitle = $source->replaceVariables($this->getSubTitle());
        $times = array_map(function ($time) use ($source) {
            $time = $source->replaceVariables($time);
            $this->throwIfInvalidNumber($time);
            return (int)$time;
        }, $this->getTime());

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        $player->sendTitle($title, $subtitle, ...$times);

        yield Await::ALL;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
            new ExampleInput("@action.sendTitle.form.title", "aieuo", $this->getTitle()),
            new ExampleInput("@action.sendTitle.form.subtitle", "aieuo", $this->getSubTitle()),
            new ExampleNumberInput("@action.sendTitle.form.fadein", "-1", $this->fadein, true, -1),
            new ExampleNumberInput("@action.sendTitle.form.stay", "-1", $this->stay, true, -1),
            new ExampleNumberInput("@action.sendTitle.form.fadeout", "-1", $this->fadeout, true, -1),
        ];
    }

    public function parseFromFormData(array $data): array {
        if ($data[1] === "" and $data[2] === "") {
            throw new InvalidFormValueException("@form.insufficient", 1);
        }
        return $data;
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setPlayerVariableName($content[0]);
        $this->setTitle($content[1], $content[2]);
        if (isset($content[5])) {
            $this->setTime($content[3], $content[4], $content[5]);
        }
        return $this;
    }

    public function serializeContents(): array {
        return array_merge([$this->getPlayerVariableName(), $this->getTitle(), $this->getSubTitle()], $this->getTime());
    }
}
