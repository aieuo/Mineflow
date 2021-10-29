<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\inventory;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;

class ClearInventory extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;

    protected string $id = self::CLEAR_INVENTORY;

    protected string $name = "action.clearInventory.name";
    protected string $detail = "action.clearInventory.detail";
    protected array $detailDefaultReplace = ["player"];

    protected string $category = Category::INVENTORY;

    public function __construct(string $player = "") {
        $this->setPlayerVariableName($player);
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "";
    }

    public function getDetail(): string {
        return Language::get($this->detail, [$this->getPlayerVariableName()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $player = $this->getOnlinePlayer($source);

        $player->getInventory()->clearAll(true);
        yield FlowItemExecutor::CONTINUE;
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