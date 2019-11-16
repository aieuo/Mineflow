<?php

namespace aieuo\mineflow\action\process;

use pocketmine\entity\Entity;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\action\process\Process;
use aieuo\mineflow\FormAPI\element\Toggle;

class SendTitle extends Process {

    protected $id = self::SEND_TITLE;

    protected $name = "@action.sendTitle.name";
    protected $description = "@action.sendTitle.description";
    protected $detail = "action.sendTitle.detail";

    protected $category = Categories::CATEGRY_ACTION_MESSAGE;

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;

    /** @var string */
    private $title;
    /** @var string */
    private $subtitle;
    /** @var int */
    private $fadeIn = -1;
    /** @var int */
    private $stay = -1;
    /** @var int */
    private $fadeOut = -1;

    public function __construct(string $title = "", string $subtitle = "", int $fadeIn = -1, int $stay = -1, int $fadeOut = -1) {
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
        $this->fadeIn = $fadeIn;
        $this->stay = $stay;
        $this->fadeOut = $fadeOut;
        return $this;
    }

    public function getTime(): array {
        return [$this->fadeIn, $this->stay, $this->fadeOut];
    }

    public function isDataValid(): bool {
        return $this->getTitle() === "" or $this->getSubTitle() === "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getTitle(), $this->getSubTitle()]);
    }

    public function execute(?Entity $target, ?Recipe $original = null): ?bool {
        if (!($target instanceof Player)) return false;

        if (!$this->isDataValid()) {
            $target->sendMessage(Language::get("invalid.contents", [$this->getName()]));
            return false;
        }

        $target->addTitle($this->getTitle(), $this->getSubTitle(), ...$this->getTime());
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []) {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@action.sendTitle.form.title", Language::get("form.example", ["aieuo"]), $default[1] ?? $this->getTitle()),
                new Input("@action.sendTitle.form.subtitle", Language::get("form.example", ["aieuo"]), $default[2] ?? $this->getSubTitle()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $status = true;
        $errors = [];
        if ($data[1] === "" and $data[2] === "") {
            $status = false;
            $errors = [["@form.insufficient", 1], ["@form.insufficient", 2]];
        }
        return ["status" => $status, "contents" => [$data[1], $data[2]], "cancel" => $data[3], "errors" => $errors];
    }

    public function parseFromSaveData(array $content): ?Process {
        if (!isset($content[1]) or !is_string($content[0]) or !is_string($content[1])) return null;
        $this->setTitle($content[0], $content[1]);
        if (isset($content[4])) {
            $this->setTime((int)$content[2], (int)$content[3], (int)$content[4]);
        }
        return $this;
    }

    public function serializeContents(): array {
        return array_merge([$this->getTitle(), $this->getSubTitle()], $this->getTime());
    }
}