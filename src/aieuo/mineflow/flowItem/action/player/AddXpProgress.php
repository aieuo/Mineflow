<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\utils\Language;

class AddXpProgress extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;

    protected string $id = self::ADD_XP_PROGRESS;

    protected string $name = "action.addXp.name";
    protected string $detail = "action.addXp.detail";
    protected array $detailDefaultReplace = ["player", "value"];

    protected string $category = FlowItemCategory::PLAYER;

    private string $xp;

    public function __construct(string $player = "", string $damage = "") {
        $this->setPlayerVariableName($player);
        $this->xp = $damage;
    }

    public function setXp(string $xp): void {
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

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $xp = $source->replaceVariables($this->getXp());
        $this->throwIfInvalidNumber($xp);

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        $new = $player->getXpManager()->getCurrentTotalXp() + (int)$xp;
        if ($new < 0) $xp = -$player->getXpManager()->getCurrentTotalXp();
        $player->getXpManager()->addXp((int)$xp);
        yield true;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
            new ExampleNumberInput("@action.addXp.form.xp", "10", $this->getXp(), true),
        ];
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