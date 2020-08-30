<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\condition\ConditionContainer;
use aieuo\mineflow\flowItem\condition\ConditionContainerTrait;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\ExampleNumberInput;
use aieuo\mineflow\ui\FlowItemForm;
use aieuo\mineflow\variable\NumberVariable;
use pocketmine\Player;
use aieuo\mineflow\utils\Session;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\ui\ConditionContainerForm;
use aieuo\mineflow\ui\ActionForm;
use aieuo\mineflow\ui\ActionContainerForm;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\element\Button;

class WhileTaskAction extends Action implements ActionContainer, ConditionContainer {
    use ActionContainerTrait;
    use ConditionContainerTrait;

    protected $id = self::ACTION_WHILE_TASK;

    protected $name = "action.whileTask.name";
    protected $detail = "action.whileTask.description";

    protected $category = Category::SCRIPT;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    protected $permission = self::PERMISSION_LEVEL_1;

    /** @var int */
    private $interval;
    /** @var int */
    private $limit = -1;
    /** @var int */
    private $taskId;

    /** @var int */
    private $loopCount = 0;

    public function __construct(array $conditions = [], array $actions = [], int $interval = 20, ?string $customName = null) {
        $this->setConditions($conditions);
        $this->setActions($actions);
        $this->interval = $interval;
        $this->setCustomName($customName);
    }

    public function setLimit(int $limit): void {
        $this->limit = $limit;
    }

    public function getLimit(): int {
        return $this->limit;
    }

    public function setInterval(int $interval): void {
        $this->interval = $interval;
    }

    public function getInterval(): int {
        return $this->interval;
    }

    public function setTaskId(int $id) {
        $this->taskId = $id;
    }

    public function getDetail(): string {
        $details = ["", "=========whileTask(".$this->getInterval().")========="];
        foreach ($this->conditions as $condition) {
            $details[] = $condition->getDetail();
        }
        $details[] = "~~~~~~~~~~~~~~~~~~~~~~~~~~~";
        foreach ($this->actions as $action) {
            $details[] = $action->getDetail();
        }
        $details[] = "================================";
        return implode("\n", $details);
    }

    public function getContainerName(): string {
        return empty($this->getCustomName()) ? $this->getName() : $this->getCustomName();
    }

    public function execute(Recipe $origin) {
        $wait = new Wait(strval($this->getInterval() / 20));
        while (true) {
            $origin->addVariable(new NumberVariable($this->loopCount, "i"));
            foreach ($this->conditions as $i => $condition) {
                if ($condition->execute($origin) !== true) {
                    $origin->resume();
                    return true;
                }
            }

            yield from $this->executeActions($origin);
            yield from $wait->execute($origin);
        }
    }

    public function hasCustomMenu(): bool {
        return true;
    }

    public function sendCustomMenu(Player $player, array $messages = []): void {
        $detail = trim($this->getDetail());
        (new ListForm($this->getName()))
            ->setContent(empty($detail) ? "@recipe.noActions" : $detail)
            ->addButtons([
                new Button("@form.back"),
                new Button("@condition.edit"),
                new Button("@action.edit"),
                new Button("@action.whileTask.editInterval"),
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
                        (new ActionContainerForm)->sendActionList($player, $parent);
                        break;
                    case 1:
                        (new ConditionContainerForm)->sendConditionList($player, $this);
                        break;
                    case 2:
                        (new ActionContainerForm)->sendActionList($player, $this);
                        break;
                    case 3:
                        $this->sendSetWhileIntervalForm($player);
                        break;
                    case 4:
                        (new FlowItemForm)->sendChangeName($player, $this, $parent);
                        break;
                    case 5:
                        (new ActionContainerForm)->sendMoveAction($player, $parent, array_search($this, $parent->getActions(), true));
                        break;
                    case 6:
                        (new ActionForm)->sendConfirmDelete($player, $this, $parent);
                        break;
                }
            })->onClose(function (Player $player) {
                Session::getSession($player)->removeAll();
            })->addMessages($messages)->show($player);
    }

    public function sendSetWhileIntervalForm(Player $player, array $default = [], array $errors = []) {
        (new CustomForm("@action.repeat.editCount"))
            ->setContents([
                new ExampleNumberInput("@action.whileTask.interval", "20", $default[0] ?? $this->getInterval(), true, 1),
                new CancelToggle()
            ])->onReceive(function (Player $player, array $data) {
                if ($data[1]) {
                    $this->sendCustomMenu($player, ["@form.cancelled"]);
                    return;
                }

                $this->setInterval((int)$data[0]);
                $this->sendCustomMenu($player, ["@form.changed"]);
            })->addErrors($errors)->show($player);
    }

    public function loadSaveData(array $contents): Action {
        foreach ($contents[0] as $content) {
            $condition = Condition::loadSaveDataStatic($content);
            $this->addCondition($condition);
        }

        foreach ($contents[1] as $content) {
            $action = Action::loadSaveDataStatic($content);
            $this->addAction($action);
        }

        $this->setInterval($contents[2] ?? 20);
        $this->setLimit($contents[3] ?? -1);
        return $this;
    }

    public function serializeContents(): array {
        return  [
            $this->conditions,
            $this->actions,
            $this->interval,
            $this->limit,
        ];
    }

    public function isDataValid(): bool {
        return true;
    }

    public function allowDirectCall(): bool {
        return false;
    }

    public function __clone() {
        $conditions = [];
        foreach ($this->getConditions() as $k => $condition) {
            $conditions[$k] = clone $condition;
        }
        $this->setConditions($conditions);

        $actions = [];
        foreach ($this->getActions() as $k => $action) {
            $actions[$k] = clone $action;
        }
        $this->setActions($actions);
    }
}