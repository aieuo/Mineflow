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
use aieuo\mineflow\Main;
use aieuo\mineflow\utils\Language;

class RemovePermission extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;

    protected string $name = "action.removePermission.name";
    protected string $detail = "action.removePermission.detail";
    protected array $detailDefaultReplace = ["player", "permission"];

    public function __construct(string $player = "", private string $playerPermission = "") {
        parent::__construct(self::REMOVE_PERMISSION, FlowItemCategory::PLAYER);

        $this->setPlayerVariableName($player);
    }

    public function getPermissions(): array {
        return [self::PERMISSION_PERMISSION];
    }

    public function setPlayerPermission(string $playerPermission): void {
        $this->playerPermission = $playerPermission;
    }

    public function getPlayerPermission(): string {
        return $this->playerPermission;
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "" and $this->playerPermission !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPlayerVariableName(), $this->getPlayerPermission()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $permission = $source->replaceVariables($this->getPlayerPermission());

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        $player->addAttachment(Main::getInstance(), $permission, false);
        yield true;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
            new ExampleInput("@condition.hasPermission.form.permission", "mineflow.customcommand.op", $this->getPlayerPermission(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setPlayerVariableName($content[0]);
        $this->setPlayerPermission($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->getPlayerPermission()];
    }
}