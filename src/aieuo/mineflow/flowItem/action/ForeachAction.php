<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemContainer;
use aieuo\mineflow\flowItem\FlowItemContainerTrait;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\element\ExampleInput;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\ui\FlowItemContainerForm;
use aieuo\mineflow\ui\FlowItemForm;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Session;
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

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

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

        $details = ["", "== foreach(".$repeat.") =="];
        foreach ($this->getItems(FlowItemContainer::ACTION) as $action) {
            $details[] = $action->getDetail();
        }
        $details[] = "================================";
        return implode("\n", $details);
    }

    public function getContainerName(): string {
        return empty($this->getCustomName()) ? $this->getName() : $this->getCustomName();
    }

    public function execute(Recipe $origin) {
        $listName = $origin->replaceVariables($this->listVariableName);
        $list = $origin->getVariable($listName) ?? Main::getVariableHelper()->getNested($listName);
        $keyName = $origin->replaceVariables($this->keyVariableName);
        $valueName = $origin->replaceVariables($this->valueVariableName);

        if (!($list instanceof ListVariable)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.foreach.error.notVariable", [$listName]));
        }

        foreach ($list->getValue() as $key => $value) {
            $keyVariable = is_numeric($key) ? new NumberVariable($key, $keyName) : new StringVariable($key, $keyName);
            $origin->addVariable($keyVariable);

            $valueVariable = clone $value;
            $valueVariable->setName($valueName);
            $origin->addVariable($valueVariable);

            yield from $this->executeAll($origin, FlowItemContainer::ACTION);
        }
        $origin->resume();
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

    public function sendSettingCounter(Player $player, array $default = [], array $errors = []) {
        (new CustomForm("@action.for.setting"))
            ->setContents([
                new ExampleInput("@action.foreach.listVariableName","list", $default[0] ?? $this->getListVariableName(), true),
                new ExampleInput("@action.foreach.keyVariableName", "key", $default[1] ?? $this->getKeyVariableName(), true),
                new ExampleInput("@action.foreach.valueVariableName", "value", $default[2] ?? $this->getValueVariableName(), true),
            ])->onReceive(function (Player $player, array $data) {
                $this->setListVariableName($data[0]);
                $this->setKeyVariableName($data[1]);
                $this->setValueVariableName($data[2]);
                $this->sendCustomMenu($player, ["@form.changed"]);
            })->addErrors($errors)->show($player);
    }

    public function loadSaveData(array $contents): FlowItem {
        foreach ($contents[0] as $content) {
            $action = FlowItem::loadSaveDataStatic($content);
            $this->addItem($action, FlowItemContainer::ACTION);
        }

        $this->setListVariableName($contents[1]);
        $this->setKeyVariableName($contents[2]);
        $this->setValueVariableName($contents[3]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getItems(FlowItemContainer::ACTION), $this->listVariableName, $this->keyVariableName, $this->valueVariableName,];
    }

    public function isDataValid(): bool {
        return true;
    }

    public function allowDirectCall(): bool {
        return false;
    }

    public function __clone() {
        $actions = [];
        foreach ($this->getItems(FlowItemContainer::ACTION) as $k => $action) {
            $actions[$k] = clone $action;
        }
        $this->setItems($actions, FlowItemContainer::ACTION);
    }
}