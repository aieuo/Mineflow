<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemContainer;
use aieuo\mineflow\flowItem\FlowItemContainerTrait;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\ui\FlowItemContainerForm;
use aieuo\mineflow\ui\FlowItemForm;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\StringVariable;
use pocketmine\player\Player;

class ForeachAction extends FlowItem implements FlowItemContainer {
    use FlowItemContainerTrait;

    protected string $id = self::ACTION_FOREACH;

    protected string $name = "action.foreach.name";
    protected string $detail = "action.foreach.description";

    protected string $category = FlowItemCategory::SCRIPT_LOOP;

    protected int $permission = self::PERMISSION_LEVEL_1;

    private string $listVariableName = "list";
    private string $keyVariableName = "key";
    private string $valueVariableName = "value";

    public function __construct(array $actions = [], ?string $customName = null) {
        $this->setActions($actions);
        $this->setCustomName($customName);
    }

    public function setValueVariableName(string $count): void {
        $this->valueVariableName = $count;
    }

    public function getValueVariableName(): string {
        return $this->valueVariableName;
    }

    public function setKeyVariableName(string $keyVariableName): self {
        $this->keyVariableName = $keyVariableName;
        return $this;
    }

    public function getKeyVariableName(): string {
        return $this->keyVariableName;
    }

    public function setListVariableName(string $listVariableName): self {
        $this->listVariableName = $listVariableName;
        return $this;
    }

    public function getListVariableName(): string {
        return $this->listVariableName;
    }

    public function getDetail(): string {
        $repeat = $this->getListVariableName()." as ".$this->getKeyVariableName()." => ".$this->getValueVariableName();

        $details = ["", "§7==§f foreach(".$repeat.") §7==§f"];
        foreach ($this->getActions() as $action) {
            $details[] = $action->getDetail();
        }
        $details[] = "§7================================§f";
        return implode("\n", $details);
    }

    public function getContainerName(): string {
        return empty($this->getCustomName()) ? $this->getName() : $this->getCustomName();
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $listName = $source->replaceVariables($this->listVariableName);
        $list = $source->getVariable($listName) ?? Mineflow::getVariableHelper()->getNested($listName);
        $keyName = $source->replaceVariables($this->keyVariableName);
        $valueName = $source->replaceVariables($this->valueVariableName);

        if (!($list instanceof ListVariable)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.foreach.error.notVariable", [$listName]));
        }

        foreach ($list->getValue() as $key => $value) {
            $keyVariable = is_numeric($key) ? new NumberVariable($key) : new StringVariable($key);
            $valueVariable = clone $value;

            yield from (new FlowItemExecutor($this->getActions(), $source->getTarget(), [
                $keyName => $keyVariable,
                $valueName => $valueVariable
            ], $source))->executeGenerator();
        }
        yield true;
    }

    public function hasCustomMenu(): bool {
        return true;
    }

    public function getCustomMenuButtons(): array {
        return [
            new Button("@action.edit", fn(Player $player) => (new FlowItemContainerForm)->sendActionList($player, $this, FlowItemContainer::ACTION)),
            new Button("@action.for.setting", fn(Player $player) => $this->sendSettingCounter($player)),
        ];
    }

    public function sendSettingCounter(Player $player): void {
        (new CustomForm("@action.for.setting"))
            ->setContents([
                new ExampleInput("@action.foreach.listVariableName", "list", $this->getListVariableName(), true),
                new ExampleInput("@action.foreach.keyVariableName", "key", $this->getKeyVariableName(), true),
                new ExampleInput("@action.foreach.valueVariableName", "value", $this->getValueVariableName(), true),
            ])->onReceive(function (Player $player, array $data) {
                $this->setListVariableName($data[0]);
                $this->setKeyVariableName($data[1]);
                $this->setValueVariableName($data[2]);
                (new FlowItemForm)->sendFlowItemCustomMenu($player, $this, FlowItemContainer::ACTION, ["@form.changed"]);
            })->show($player);
    }

    public function loadSaveData(array $contents): FlowItem {
        foreach ($contents[0] as $content) {
            $action = FlowItem::loadEachSaveData($content);
            $this->addAction($action);
        }

        $this->setListVariableName($contents[1]);
        $this->setKeyVariableName($contents[2]);
        $this->setValueVariableName($contents[3]);
        return $this;
    }

    public function getAddingVariables(): array {
        return [
            $this->getKeyVariableName() => new DummyVariable(DummyVariable::UNKNOWN),
            $this->getValueVariableName() => new DummyVariable(DummyVariable::UNKNOWN),
        ];
    }

    public function serializeContents(): array {
        return [$this->getActions(), $this->listVariableName, $this->keyVariableName, $this->valueVariableName,];
    }

    public function isDataValid(): bool {
        return true;
    }

    public function allowDirectCall(): bool {
        return false;
    }

    public function __clone() {
        $actions = [];
        foreach ($this->getActions() as $k => $action) {
            $actions[$k] = clone $action;
        }
        $this->setActions($actions);
    }
}
