<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemContainer;
use aieuo\mineflow\flowItem\FlowItemContainerTrait;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\ui\FlowItemContainerForm;
use aieuo\mineflow\ui\FlowItemForm;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Session;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\StringVariable;
use pocketmine\Player;

class ForeachAction extends FlowItem implements FlowItemContainer {
    use FlowItemContainerTrait;

    protected $id = self::ACTION_FOREACH;

    protected $name = "action.foreach.name";
    protected $detail = "action.foreach.description";

    protected $category = Category::SCRIPT;

    protected $permission = self::PERMISSION_LEVEL_1;

    /** @var string */
    private $listVariableName = "list";
    /** @var string */
    private $keyVariableName = "key";
    /** @var string */
    private $valueVariableName = "value";

    /** @var array */
    private $counter;

    public function __construct(array $actions = [], ?string $customName = null) {
        $this->setItems($actions, FlowItemContainer::ACTION);
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

    public function execute(Recipe $source): \Generator {
        $listName = $source->replaceVariables($this->listVariableName);
        $list = $source->getVariable($listName) ?? Main::getVariableHelper()->getNested($listName);
        $keyName = $source->replaceVariables($this->keyVariableName);
        $valueName = $source->replaceVariables($this->valueVariableName);

        if (!($list instanceof ListVariable)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.foreach.error.notVariable", [$listName]));
        }

        foreach ($list->getValue() as $key => $value) {
            $keyVariable = is_numeric($key) ? new NumberVariable($key, $keyName) : new StringVariable($key, $keyName);
            $source->addVariable($keyVariable);

            $valueVariable = clone $value;
            $valueVariable->setName($valueName);
            $source->addVariable($valueVariable);

            yield from $this->executeAll($source, FlowItemContainer::ACTION);
        }
        $source->resume();
        yield true;
    }

    public function hasCustomMenu(): bool {
        return true;
    }

    public function sendCustomMenu(Player $player, array $messages = []): void {
        $detail = trim($this->getDetail());
        (new ListForm($this->getName()))->setContent(empty($detail) ? "@recipe.noActions" : $detail)
            ->addButtons([
                new Button("@form.back"),
                new Button("@action.edit"),
                new Button("@action.for.setting"),
                new Button("@form.home.rename.title"),
                new Button("@form.move"),
                new Button("@form.delete"),
            ])->onReceive(function (Player $player, int $data) {
                $session = Session::getSession($player);
                $parents = $session->get("parents");
                /** @var FlowItemContainer $parent */
                $parent = end($parents);
                switch ($data) {
                    case 0:
                        $session->pop("parents");
                        (new FlowItemContainerForm)->sendActionList($player, $parent, FlowItemContainer::ACTION);
                        break;
                    case 1:
                        (new FlowItemContainerForm)->sendActionList($player, $this, FlowItemContainer::ACTION);
                        break;
                    case 2:
                        $this->sendSettingCounter($player);
                        break;
                    case 3:
                        (new FlowItemForm)->sendChangeName($player, $this, $parent, FlowItemContainer::ACTION);
                        break;
                    case 4:
                        (new FlowItemContainerForm)->sendMoveAction($player, $parent, FlowItemContainer::ACTION, array_search($this, $parent->getActions(), true));
                        break;
                    case 5:
                        (new FlowItemForm)->sendConfirmDelete($player, $this, $parent, FlowItemContainer::ACTION);
                        break;
                }
            })->onClose(function (Player $player) {
                Session::getSession($player)->removeAll();
            })->addMessages($messages)->show($player);
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
                $this->sendCustomMenu($player, ["@form.changed"]);
            })->show($player);
    }

    public function loadSaveData(array $contents): FlowItem {
        foreach ($contents[0] as $content) {
            $action = FlowItem::loadEachSaveData($content);
            $this->addItem($action, FlowItemContainer::ACTION);
        }

        $this->setListVariableName($contents[1]);
        $this->setKeyVariableName($contents[2]);
        $this->setValueVariableName($contents[3]);
        return $this;
    }

    public function getAddingVariables(): array {
        return [
            new DummyVariable($this->getKeyVariableName(), DummyVariable::UNKNOWN),
            new DummyVariable($this->getValueVariableName(), DummyVariable::UNKNOWN),
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
        $this->setItems($actions, FlowItemContainer::ACTION);
    }
}