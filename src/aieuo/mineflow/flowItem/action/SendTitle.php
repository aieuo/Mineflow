<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\formAPI\Form;
use pocketmine\entity\Entity;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Categories;
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
    protected $detailDefaultReplace = ["player", "title", "subtitle"];

    protected $category = Categories::CATEGORY_ACTION_MESSAGE;

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;
    protected $returnValueType = self::RETURN_NONE;

    /** @var string */
    private $title;
    /** @var string */
    private $subtitle;
    /** @var string */
    private $fadeIn = "-1";
    /** @var string */
    private $stay = "-1";
    /** @var string */
    private $fadeOut = "-1";

    public function __construct(string $name = "", string $title = "", string $subtitle = "", int $fadeIn = -1, int $stay = -1, int $fadeOut = -1) {
        $this->playerVariableName = $name;
        $this->title = $title;
        $this->subtitle = $subtitle;
        $this->fadeIn = $fadeIn;
        $this->stay = $stay;
        $this->fadeOut = $fadeOut;
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

    public function setTime(int $fadeIn = -1, int $stay = -1, int $fadeOut = -1): self {
        $this->fadeIn = (string)$fadeIn;
        $this->stay = (string)$stay;
        $this->fadeOut = (string)$fadeOut;
        return $this;
    }

    public function getTime(): array {
        return [$this->fadeIn, $this->stay, $this->fadeOut];
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "" and ($this->getTitle() !== "" or $this->getSubTitle() !== "");
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPlayerVariableName(), $this->getTitle(), $this->getSubTitle()]);
    }

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $title = $origin->replaceVariables($this->getTitle());
        $subtitle = $origin->replaceVariables($this->getSubTitle());
        $times = array_map(function ($time) use ($origin) {
            return $origin->replaceVariables($time);
        }, $this->getTime());

        $player = $this->getPlayer($origin);
        $this->throwIfInvalidPlayer($player);

        $player->addTitle($title, $subtitle, ...$times);
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@flowItem.form.target.player", Language::get("form.example", ["target"]), $default[1] ?? $this->getPlayerVariableName()),
                new Input("@action.sendTitle.form.title", Language::get("form.example", ["aieuo"]), $default[2] ?? $this->getTitle()),
                new Input("@action.sendTitle.form.subtitle", Language::get("form.example", ["aieuo"]), $default[3] ?? $this->getSubTitle()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[1] === "") $data[1] = "target";
        if ($data[2] === "" and $data[3] === "") {
            $errors = [["@form.insufficient", 2], ["@form.insufficient", 3]];
        }
        return ["status" => empty($errors), "contents" => [$data[1], $data[2], $data[3], "-1", "-1", "-1"], "cancel" => $data[4], "errors" => $errors];
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
