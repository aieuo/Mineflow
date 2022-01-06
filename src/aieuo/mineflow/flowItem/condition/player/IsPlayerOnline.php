<?php

namespace aieuo\mineflow\flowItem\condition\player;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\utils\Language;

class IsPlayerOnline extends FlowItem implements Condition, PlayerFlowItem {
    use PlayerFlowItemTrait;

    protected string $name = "condition.isPlayerOnline.name";
    protected string $detail = "condition.isPlayerOnline.detail";
    protected array $detailDefaultReplace = ["player"];

    public function __construct(string $player = "") {
        parent::__construct(self::IS_PLAYER_ONLINE, FlowItemCategory::PLAYER);

        $this->setPlayerVariableName($player);
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== null;
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPlayerVariableName()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player, false);

        yield true;
        return $player->isOnline();
    }

    public function getEditFormElements(array $variables): array {
        return [
            new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setPlayerVariableName($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName()];
    }
}