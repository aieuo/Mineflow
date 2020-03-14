<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\condition\ConditionContainer;
use aieuo\mineflow\flowItem\condition\ConditionContainerTrait;
use aieuo\mineflow\variable\NumberVariable;
use pocketmine\entity\Entity;
use pocketmine\Player;
use aieuo\mineflow\utils\Session;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\ui\ScriptForm;
use aieuo\mineflow\ui\ConditionContainerForm;
use aieuo\mineflow\ui\ActionForm;
use aieuo\mineflow\ui\ActionContainerForm;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\Main;
use aieuo\mineflow\task\WhileActionTask;

class WhileAction extends Action implements ActionContainer, ConditionContainer {
    use ActionContainerTrait, ConditionContainerTrait;

    protected $id = self::ACTION_WHILE;

    protected $name = "action.while.name";
    protected $detail = "action.while.description";

    protected $category = Categories::CATEGORY_ACTION_SCRIPT;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    /** @var int */
    private $interval = 20;
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
        $details = ["", "=============while============="];
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

    public function execute(?Entity $target, Recipe $origin): ?bool {
        $script = clone $this;
        $origin->wait();
        $handler = Main::getInstance()->getScheduler()->scheduleRepeatingTask(new WhileActionTask($script, $target, $origin), $this->interval);
        $script->setTaskId($handler->getTaskId());
        return true;
    }

    public function check(?Entity $target, Recipe $origin) {
        $origin->addVariable(new NumberVariable($this->loopCount, "i"));
        foreach ($this->conditions as $condition) {
            $result = $condition->execute($target, $origin);

            if ($result !== true) {
                Main::getInstance()->getScheduler()->cancelTask($this->taskId);
                if ($origin instanceof Recipe) $origin->resume();
                return;
            }
        }

        foreach ($this->actions as $action) {
            $result = $action->execute($target, $origin);
            if ($result === null) return null;
        }
        $this->loopCount ++;
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
                new Button("@action.while.editInterval"),
                new Button("@form.delete"),
            ])->onReceive(function (Player $player, ?int $data) {
                $session = Session::getSession($player);
                if ($data === null) {
                    $session->removeAll();
                    return;
                }
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
            })->addMessages($messages)->show($player);
    }

    public function loadSaveData(array $contents): ?Action {
        if (!isset($contents[1])) return null;
        foreach ($contents[0] as $content) {
            switch ($content["type"]) {
                case Recipe::CONTENT_TYPE_CONDITION:
                    $condition = Condition::loadSaveDataStatic($content);
                    break;
                default:
                    return null;
            }
            if ($condition === null) return null;
            $this->addCondition($condition);
        }

        foreach ($contents[1] as $content) {
            switch ($content["type"]) {
                case Recipe::CONTENT_TYPE_PROCESS:
                    $action = Action::loadSaveDataStatic($content);
                    break;
                default:
                    return null;
            }
            if ($action === null) return null;
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
}