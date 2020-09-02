<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\ExampleInput;
use aieuo\mineflow\formAPI\element\ExampleNumberInput;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;

class AddXpProgress extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;

    protected $id = self::ADD_XP_PROGRESS;

    protected $name = "action.addXp.name";
    protected $detail = "action.addXp.detail";
    protected $detailDefaultReplace = ["player", "value"];

    protected $category = Category::PLAYER;

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;

    /** @var string */
    private $xp;

    public function __construct(string $player = "target", string $damage = "") {
        $this->setPlayerVariableName($player);
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

    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $xp = $origin->replaceVariables($this->getXp());
        $this->throwIfInvalidNumber($xp);

        $player = $this->getPlayer($origin);
        $this->throwIfInvalidPlayer($player);

        $new = $player->getCurrentTotalXp() + (int)$xp;
        if ($new < 0) $xp = -$player->getCurrentTotalXp();
        $player->addXp($xp);
        yield true;
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@flowItem.form.target.player", "target", $default[1] ?? $this->getPlayerVariableName(), true),
                new ExampleNumberInput("@action.addXp.form.xp", "10", $default[2] ?? $this->getXp(), true),
                new CancelToggle()
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], $data[2]], "cancel" => $data[3], "errors" => []];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setPlayerVariableName($content[0]);
        $this->setXp($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->getXp()];
    }
}