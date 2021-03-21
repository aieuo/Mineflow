<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\Main;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;

class AddPermission extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;

    protected $id = self::ADD_PERMISSION;

    protected $name = "action.addPermission.name";
    protected $detail = "action.addPermission.detail";
    protected $detailDefaultReplace = ["player", "permission"];

    protected $category = Category::PLAYER;

    protected $permission = self::PERMISSION_LEVEL_1;

    /** @var string */
    private $playerPermission;

    public function __construct(string $player = "", string $permission = "") {
        $this->setPlayerVariableName($player);
        $this->playerPermission = $permission;
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

    public function execute(Recipe $source): \Generator {
        $this->throwIfCannotExecute();

        $permission = $source->replaceVariables($this->getPlayerPermission());

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        $player->addAttachment(Main::getInstance(), $permission, true);
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