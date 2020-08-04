<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\Main;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Toggle;

class SendTitle extends Action implements PlayerFlowItem {
    use PlayerFlowItemTrait;

    protected $id = self::SEND_TITLE;

    protected $name = "action.sendTitle.name";
    protected $detail = "action.sendTitle.detail";
    protected $detailDefaultReplace = ["player", "title", "subtitle", "fadein", "stay", "fadeout"];

    protected $category = Category::PLAYER;

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;

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

    public function __construct(string $player = "target", string $title = "", string $subtitle = "", string $fadeIn = "-1", string $stay = "-1", string $fadeOut = "-1") {
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

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $title = $origin->replaceVariables($this->getTitle());
        $subtitle = $origin->replaceVariables($this->getSubTitle());
        $times = array_map(function ($time) use ($origin) {
            $time = $origin->replaceVariables($time);
            $this->throwIfInvalidNumber($time);
            return (int)$time;
        }, $this->getTime());

        $player = $this->getPlayer($origin);
        $this->throwIfInvalidPlayer($player);

        $player->sendTitle($title, $subtitle, ...$times);
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@flowItem.form.target.player", Language::get("form.example", ["target"]), $default[1] ?? $this->getPlayerVariableName()),
                new Input("@action.sendTitle.form.title", Language::get("form.example", ["aieuo"]), $default[2] ?? $this->getTitle()),
                new Input("@action.sendTitle.form.subtitle", Language::get("form.example", ["aieuo"]), $default[3] ?? $this->getSubTitle()),
                new Input("@action.sendTitle.form.fadein", Language::get("form.example", ["-1"]), $default[4] ?? $this->fadein),
                new Input("@action.sendTitle.form.stay", Language::get("form.example", ["-1"]), $default[5] ?? $this->stay),
                new Input("@action.sendTitle.form.fadeout", Language::get("form.example", ["-1"]), $default[6] ?? $this->fadeout),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[1] === "") $data[1] = "target";
        if ($data[2] === "" and $data[3] === "") {
            $errors = [["@form.insufficient", 2], ["@form.insufficient", 3]];
        }
        for ($i=4; $i<=6; $i++) {
            if ($data[$i] === "") $data[$i] = "-1";
            if (!is_numeric($data[$i]) and !Main::getVariableHelper()->containsVariable($data[$i])) {
                $errors[] = ["@flowItem.error.notNumber", $i];
            }
        }
        return ["contents" => [$data[1], $data[2], $data[3], $data[4], $data[5], $data[6]], "cancel" => $data[7], "errors" => $errors];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[2])) throw new \OutOfBoundsException();
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
