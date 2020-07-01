<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\formAPI\element\Toggle;

class AddXpProgress extends Action implements PlayerFlowItem {
    use PlayerFlowItemTrait;

    protected $id = self::ADD_XP_PROGRESS;

    protected $name = "action.addXp.name";
    protected $detail = "action.addXp.detail";
    protected $detailDefaultReplace = ["player", "value"];

    protected $category = Category::PLAYER;

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;

    /** @var string */
    private $xp;

    public function __construct(string $name = "target", string $damage = "") {
        $this->playerVariableName = $name;
        $this->xp = $damage;
    }

    public function setXp(string $xp) {
        $this->xp = $xp;
    }

    public function getXp(): string {
        return $this->xp;
    }

    public function isDataValid(): bool {
        return $this->xp !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPlayerVariableName(), $this->getXp()]);
    }

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $xp = $origin->replaceVariables($this->getXp());
        $this->throwIfInvalidNumber($xp);

        $player = $this->getPlayer($origin);
        $this->throwIfInvalidPlayer($player);

        $new = $player->getCurrentTotalXp() + (int)$xp;
        if ($new < 0) $xp = -$player->getCurrentTotalXp();
        $player->addXp($xp);
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@flowItem.form.target.player", Language::get("form.example", ["target"]), $default[1] ?? $this->getPlayerVariableName()),
                new Input("@action.addXp.form.xp", Language::get("form.example", ["10"]), $default[2] ?? $this->getXp()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[1] === "") $errors[] = ["@form.insufficient", 1];
        if ($data[2] === "") {
            $errors[] = ["@form.insufficient", 2];
        } elseif (!Main::getVariableHelper()->containsVariable($data[2]) and !is_numeric($data[2])) {
            $errors[] = ["@flowItem.error.notNumber", 2];
        }
        return ["contents" => [$data[1], $data[2]], "cancel" => $data[3], "errors" => $errors];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[1])) throw new \OutOfBoundsException();
        $this->setPlayerVariableName($content[0]);
        $this->setXp($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->getXp()];
    }
}