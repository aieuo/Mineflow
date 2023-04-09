<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player\bossbar;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\utils\Bossbar;
use SOFe\AwaitGenerator\Await;

class RemoveBossbar extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    public function __construct(string $player = "", private string $barId = "") {
        parent::__construct(self::REMOVE_BOSSBAR, FlowItemCategory::BOSSBAR);

        $this->setPlayerVariableName($player);
    }

    public function getDetailDefaultReplaces(): array {
        return ["player", "id"];
    }

    public function getDetailReplaces(): array {
        return [$this->getPlayerVariableName(), $this->getBarId()];
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

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $id = $source->replaceVariables($this->getBarId());
        $player = $this->getOnlinePlayer($source);

        Bossbar::remove($player, $id);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
            new ExampleInput("@action.showBossbar.form.id", "aieuo", $this->getBarId(), true),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->setPlayerVariableName($content[0]);
        $this->setBarId($content[1]);
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->getBarId()];
    }
}
