<?php

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\base\PositionFlowItem;
use aieuo\mineflow\flowItem\base\PositionFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\PositionVariableDropdown;
use aieuo\mineflow\utils\Language;

class SetSleeping extends FlowItem implements PlayerFlowItem, PositionFlowItem {
    use PlayerFlowItemTrait, PositionFlowItemTrait;

    protected string $name = "action.setSleeping.name";
    protected string $detail = "action.setSleeping.detail";
    protected array $detailDefaultReplace = ["player", "position"];

    public function __construct(string $player = "", string $position = "") {
        parent::__construct(self::SET_SLEEPING, FlowItemCategory::PLAYER);

        $this->setPlayerVariableName($player);
        $this->setPositionVariableName($position);
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPlayerVariableName(), $this->getPositionVariableName()]);
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "" and $this->getPositionVariableName() !== "";
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        $position = $this->getPosition($source);

        $player->sleepOn($position);
        yield true;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
            new PositionVariableDropdown($variables, $this->getPositionVariableName()),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setPlayerVariableName($content[0]);
        $this->setPositionVariableName($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->getPositionVariableName()];
    }
}
