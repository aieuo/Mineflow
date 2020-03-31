<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Bossbar;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\formAPI\element\Toggle;

class ShowBossbar extends Action implements PlayerFlowItem {
    use PlayerFlowItemTrait;

    protected $id = self::SHOW_BOSSBAR;

    protected $name = "action.showBossbar.name";
    protected $detail = "action.showBossbar.detail";
    protected $detailDefaultReplace = ["player", "title", "max", "value", "id"];

    protected $category = Categories::CATEGORY_ACTION_PLAYER;

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;
    protected $returnValueType = self::RETURN_NONE;

    /** @var string */
    private $title;
    private $max;
    private $value;
    private $barId;

    public function __construct(string $name = "target", string $title = "", string $max = "", string $value = "", string $barId = "") {
        $this->playerVariableName = $name;
        $this->title = $title;
        $this->max = $max;
        $this->value = $value;
        $this->barId = $barId;
    }

    public function setTitle(string $health) {
        $this->title = $health;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function setMax(string $max): void {
        $this->max = $max;
    }

    public function getMax(): string {
        return $this->max;
    }

    public function setValue(string $value): void {
        $this->value = $value;
    }

    public function getValue(): string {
        return $this->value;
    }

    public function setBarId(string $barId): void {
        $this->barId = $barId;
    }

    public function getBarId(): string {
        return $this->barId;
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "" and $this->title !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPlayerVariableName(), $this->getTitle(), $this->getMax(), $this->getValue(), $this->getBarId()]);
    }

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $title = $origin->replaceVariables($this->getTitle());
        $max = $origin->replaceVariables($this->getMax());
        $value = $origin->replaceVariables($this->getValue());
        $id = $origin->replaceVariables($this->getBarId());

        $this->throwIfInvalidNumber($max, 1);
        $this->throwIfInvalidNumber($value);

        $player = $this->getPlayer($origin);
        $this->throwIfInvalidPlayer($player);

        Bossbar::add($player, $id, $title, (float)$max, (float)$value/(float)$max);
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@flowItem.form.target.player", Language::get("form.example", ["target"]), $default[1] ?? $this->getPlayerVariableName()),
                new Input("@action.showBossbar.form.title", Language::get("form.example", ["20"]), $default[2] ?? $this->getTitle()),
                new Input("@action.showBossbar.form.max", Language::get("form.example", ["20"]), $default[3] ?? $this->getMax()),
                new Input("@action.showBossbar.form.value", Language::get("form.example", ["20"]), $default[4] ?? $this->getValue()),
                new Input("@action.showBossbar.form.id", Language::get("form.example", ["20"]), $default[5] ?? $this->getBarId()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[1] === "") $data[1] = "target";
        for ($i=2; $i<=5; $i++) {
            if ($data[$i] === "") $errors[] = ["@form.insufficient", $i];
        }
        return ["status" => empty($errors), "contents" => [$data[1], $data[2], $data[3], $data[4], $data[5]], "cancel" => $data[6], "errors" => $errors];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[4])) throw new \OutOfBoundsException();
        $this->setPlayerVariableName($content[0]);
        $this->setTitle($content[1]);
        $this->setMax($content[2]);
        $this->setValue($content[3]);
        $this->setBarId($content[4]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->getTitle(), $this->getMax(), $this->getValue(), $this->getBarId()];
    }
}