<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Bossbar;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;

class RemoveBossbar extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;

    protected $id = self::REMOVE_BOSSBAR;

    protected $name = "action.removeBossbar.name";
    protected $detail = "action.removeBossbar.detail";
    protected $detailDefaultReplace = ["player", "id"];

    protected $category = Category::PLAYER;

    /** @var string */
    private $barId;

    public function __construct(string $player = "", string $barId = "") {
        $this->setPlayerVariableName($player);
        $this->barId = $barId;
    }

    public function setBarId(string $barId): void {
        $this->barId = $barId;
    }

    public function getBarId(): string {
        return $this->barId;
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "" and $this->barId !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPlayerVariableName(), $this->getBarId()]);
    }

    public function execute(Recipe $origin): \Generator {
        $this->throwIfCannotExecute();

        $id = $origin->replaceVariables($this->getBarId());

        $player = $this->getPlayer($origin);
        $this->throwIfInvalidPlayer($player);

        Bossbar::remove($player, $id);
        yield true;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
            new ExampleInput("@action.showBossbar.form.id", "aieuo", $this->getBarId(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setPlayerVariableName($content[0]);
        $this->setBarId($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->getBarId()];
    }
}