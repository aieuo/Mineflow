<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;

class IsPlayerOnline extends FlowItem implements Condition, PlayerFlowItem {
    use PlayerFlowItemTrait;

    protected $id = self::IS_PLAYER_ONLINE;

    protected $name = "condition.isPlayerOnline.name";
    protected $detail = "condition.isPlayerOnline.detail";
    protected $detailDefaultReplace = ["player"];

    protected $category = Category::PLAYER;

    public function __construct(string $player = "") {
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