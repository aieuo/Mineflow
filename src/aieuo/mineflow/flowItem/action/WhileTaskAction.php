<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\condition\ConditionContainer;
use aieuo\mineflow\flowItem\condition\ConditionContainerTrait;
use aieuo\mineflow\variable\NumberVariable;
use pocketmine\Player;
use aieuo\mineflow\utils\Session;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\ui\ScriptForm;
use aieuo\mineflow\ui\ConditionContainerForm;
use aieuo\mineflow\ui\ActionForm;
use aieuo\mineflow\ui\ActionContainerForm;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\Main;
use aieuo\mineflow\task\WhileActionTask;

class WhileTaskAction extends Action implements ActionContainer, ConditionContainer {
    use ActionContainerTrait, ConditionContainerTrait;

    protected $id = self::ACTION_WHILE_TASK;

    protected $name = "action.whileTask.name";
    protected $detail = "action.whileTask.description";

    protected $category = Category::SCRIPT;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    protected $permission = self::PERMISSION_LEVEL_1;

    /** @var int */
    private $interval = 20;
    /** @var int */
    private $limit = -1;
    /** @var int */
    private $taskId;

    /** @var int */
    private $loopCount = 0;

    /* @var bool */
    private $lastResult;

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

    public function execute(Recipe $origin): bool {
        $script = clone $this;
        $origin->wait();
        $handler = Main::getInstance()->getScheduler()->scheduleRepeatingTask(new WhileActionTask($script, $origin), $this->interval);
        $script->setTaskId($handler->getTaskId());
        return true;
    }

    public function check(Recipe $origin) {
        $origin->addVariable(new NumberVariable($this->loopCount, "i"));
        foreach ($this->conditions as $condition) {
            $result = $condition->execute($origin);

            if ($result !== true) {
                Main::getInstance()->getScheduler()->cancelTask($this->taskId);
                if ($origin instanceof Recipe) $origin->resume();
                return;
            }
        }

        foreach ($this->actions as $action) {
            $this->lastResult = $action->parent($this)->execute($origin);
        }
        $this->loopCount ++;
    }

    public function getLastActionResult(): ?bool {
        return $this->lastResult;
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
                new Button("@form.delete"),
            ])->onReceive(function (Player $player, int $data) {
                $session = Session::getSession($player);
                $parents = $session->get("parents");
                $parent = end($parents);
                switch ($data) {
                    case 0:
                        array_pop($parents);
                        $session->set("parents", $parents);
                        (new ActionContainerForm)->sendActionList($player, $parent);
                        break;
                    case 1:
                        (new ConditionContainerForm)->sendConditionList($player, $this);
                        break;
                    case 2:
                        (new ActionContainerForm)->sendActionList($player, $this);
                        break;
                    case 3:
                        (new ScriptForm)->sendSetWhileInterval($player, $this);
                        break;
                    case 4:
                        (new ActionForm)->sendConfirmDelete($player, $this, $parent);
                        break;
                }
            })->onClose(function (Player $player) {
                Session::getSession($player)->removeAll();
            })->addMessages($messages)->show($player);
    }

    public function loadSaveData(array $contents): Action {
        if (!isset($contents[1])) throw new \OutOfBoundsException();
        foreach ($contents[0] as $content) {
            if ($content["type"] !== Recipe::CONTENT_TYPE_CONDITION) {
                throw new \InvalidArgumentException("invalid content type: \"{$content["type"]}\"");
            }

            $condition = Condition::loadSaveDataStatic($content);
            $this->addCondition($condition);
        }

        foreach ($contents[1] as $content) {
            if ($content["type"] !== Recipe::CONTENT_TYPE_ACTION) {
                throw new \InvalidArgumentException("invalid content type: \"{$content["type"]}\"");
            }

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
}