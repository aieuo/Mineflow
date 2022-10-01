<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\utils\Bossbar;
use aieuo\mineflow\utils\Language;

class RemoveBossbar extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;

    protected string $name = "action.removeBossbar.name";
    protected string $detail = "action.removeBossbar.detail";
    protected array $detailDefaultReplace = ["player", "id"];

    public function __construct(string $player = "", private string $barId = "") {
        parent::__construct(self::REMOVE_BOSSBAR, FlowItemCategory::PLAYER);

        $this->setPlayerVariableName($player);
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

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $id = $source->replaceVariables($this->getBarId());

        $player = $this->getPlayer($source);
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