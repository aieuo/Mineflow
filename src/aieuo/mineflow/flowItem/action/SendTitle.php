<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\exception\InvalidFormValueException;
use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;

class SendTitle extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;

    protected $id = self::SEND_TITLE;

    protected $name = "action.sendTitle.name";
    protected $detail = "action.sendTitle.detail";
    protected $detailDefaultReplace = ["player", "title", "subtitle", "fadein", "stay", "fadeout"];

    protected $category = Category::PLAYER;

    /** @var string */
    private $title;
    /** @var string */
    private $subtitle;
    /** @var string */
    private $fadein;
    /** @var string */
    private $stay;
    /** @var string */
    private $fadeout;

    public function __construct(string $player = "", string $title = "", string $subtitle = "", string $fadeIn = "-1", string $stay = "-1", string $fadeOut = "-1") {
        $this->setPlayerVariableName($player);
        $this->title = $title;
        $this->subtitle = $subtitle;
        $this->fadein = $fadeIn;
        $this->stay = $stay;
        $this->fadeout = $fadeOut;
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

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPlayerVariableName(), $this->getTitle(), $this->getSubTitle(), $this->fadein, $this->stay, $this->fadeout]);
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
        yield true;
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
