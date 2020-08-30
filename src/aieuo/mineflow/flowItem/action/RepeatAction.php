<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\ui\FlowItemForm;
use pocketmine\Player;
use aieuo\mineflow\utils\Session;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\ui\ActionForm;
use aieuo\mineflow\ui\ActionContainerForm;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\ui\ScriptForm;
use aieuo\mineflow\variable\NumberVariable;

class RepeatAction extends Action implements ActionContainer {
    use ActionContainerTrait {
        resume as traitResume;
    }

    protected $id = self::ACTION_REPEAT;

    protected $name = "action.repeat.name";
    protected $detail = "action.repeat.description";

    protected $category = Category::SCRIPT;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    protected $permission = self::PERMISSION_LEVEL_1;

    /** @var string */
    private $repeatCount;

    /** @var string  */
    private $startIndex = "0";
    /** @var string */
    private $counterName = "i";

    /* @var bool */
    private $lastResult;
    /* @var int */
    private $lastIndex;

    public function __construct(array $actions = [], int $count = 1, ?string $customName = null) {
        $this->setActions($actions);
        $this->repeatCount = (string)$count;
        $this->setCustomName($customName);
    }

    public function setRepeatCount(string $count): void {
        $this->repeatCount = $count;
    }

    public function getRepeatCount(): string {
        return $this->repeatCount;
    }

    public function setStartIndex(string $startIndex): self {
        $this->startIndex = $startIndex;
        return $this;
    }

    public function getStartIndex(): string {
        return $this->startIndex;
    }

    public function setCounterName(string $counterName): self {
        $this->counterName = $counterName;
        return $this;
    }

    public function getCounterName(): string {
        return $this->counterName;
    }

    public function getDetail(): string {
        $repeat = (string)$this->getRepeatCount();
        $length = strlen($repeat) - 1;
        $left = ceil($length / 2);
        $right = $length - $left;
        $details = ["", str_repeat("=", 12-$left)."repeat(".$repeat.")".str_repeat("=", 12-$right)];
        foreach ($this->actions as $action) {
            $details[] = $action->getDetail();
        }
        $details[] = "================================";
        return implode("\n", $details);
    }

    public function getContainerName(): string {
        return empty($this->getCustomName()) ? $this->getName() : $this->getCustomName();
    }

    public function execute(Recipe $origin, int $startIndex = 0): bool {
        $count = $origin->replaceVariables($this->repeatCount);
        $this->throwIfInvalidNumber($count, 1);

        $start = $origin->replaceVariables($this->startIndex);
        $this->throwIfInvalidNumber($start);

        $name = $this->counterName;
        $start = (int)$start + $startIndex;
        $end = $start + (int)$count - $startIndex;

        for ($i=$start; $i<$end; $i++) {
            $this->lastIndex = $i;
            $origin->addVariable(new NumberVariable($i, $name));
            if (!$this->executeActions($origin, $this->getParent())) return false;
            if ($this->wait or $this->isWaiting()) return true;
        }
        $this->getParent()->resume();
        return true;
    }

    public function resume() {
        $last = $this->next;

        $this->wait = false;
        $this->next = null;

        if (!$this->isWaiting()) return;

        $this->waiting = false;

        $this->executeActions(...$last);
        $this->execute($last[0], $this->lastIndex + 1);
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
                new Button("@action.edit"),
                new Button("@action.repeat.editCount"),
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
                        (new ActionContainerForm)->sendActionList($player, $this);
                        break;
                    case 2:
                        (new ScriptForm)->sendSetRepeatCount($player, $this);
                        break;
                    case 3:
                        (new FlowItemForm)->sendChangeName($player, $this, $parent);
                        break;
                    case 4:
                        (new ActionContainerForm)->sendMoveAction($player, $parent, array_search($this, $parent->getActions(), true));
                        break;
                    case 5:
                        (new ActionForm)->sendConfirmDelete($player, $this, $parent);
                        break;
                }
            })->onClose(function (Player $player) {
                Session::getSession($player)->removeAll();
            })->addMessages($messages)->show($player);
    }

    public function loadSaveData(array $contents): Action {
        if (!isset($contents[1])) throw new \OutOfBoundsException();
        $this->setRepeatCount((string)$contents[0]);

        foreach ($contents[1] as $content) {
            $action = Action::loadSaveDataStatic($content);
            $this->addAction($action);
        }

        if (isset($contents[2])) $this->startIndex = (string)$contents[2];
        if (isset($contents[3])) $this->counterName = $contents[3];
        return $this;
    }

    public function serializeContents(): array {
        return  [
            $this->repeatCount,
            $this->actions,
            $this->startIndex,
            $this->counterName
        ];
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