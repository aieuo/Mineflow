<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\ExampleInput;
use aieuo\mineflow\formAPI\element\ExampleNumberInput;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\ui\FlowItemForm;
use aieuo\mineflow\utils\Language;
use pocketmine\Player;
use aieuo\mineflow\utils\Session;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\ui\ActionForm;
use aieuo\mineflow\ui\ActionContainerForm;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\variable\NumberVariable;

class ForAction extends Action implements ActionContainer {
    use ActionContainerTrait {
        resume as traitResume;
    }

    protected $id = self::ACTION_FOR;

    protected $name = "action.for.name";
    protected $detail = "action.for.description";

    protected $category = Category::SCRIPT;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    protected $permission = self::PERMISSION_LEVEL_1;

    /** @var string */
    private $counterName = "i";
    /** @var string  */
    private $startIndex = "0";
    /** @var string */
    private $endIndex = "9";
    /** string */
    private $fluctuation = "1";

    /* @var bool */
    private $lastResult;

    /** @var array */
    private $counter;

    public function __construct(array $actions = [], ?string $customName = null) {
        $this->setActions($actions);
        $this->setCustomName($customName);
    }

    public function setEndIndex(string $count): void {
        $this->endIndex = $count;
    }

    public function getEndIndex(): string {
        return $this->endIndex;
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

    public function setFluctuation(string $fluctuation): void {
        $this->fluctuation = $fluctuation;
    }

    public function getFluctuation(): string {
        return $this->fluctuation;
    }

    public function getDetail(): string {
        $counter = $this->getCounterName();
        $repeat = $counter."=".$this->getStartIndex()."; ".$counter."<=".$this->getEndIndex()."; ".$counter."+=".$this->getFluctuation();
        $repeat = str_replace("+=-", "-=", $repeat);

        $details = ["", "==== for(".$repeat.") ===="];
        foreach ($this->actions as $action) {
            $details[] = $action->getDetail();
        }
        $details[] = "================================";
        return implode("\n", $details);
    }

    public function getContainerName(): string {
        return empty($this->getCustomName()) ? $this->getName() : $this->getCustomName();
    }

    public function execute(Recipe $origin, bool $first = true): bool {
        if ($first) {
            $counterName = $origin->replaceVariables($this->counterName);

            $start = $origin->replaceVariables($this->startIndex);
            $this->throwIfInvalidNumber($start);

            $end = $origin->replaceVariables($this->endIndex);
            $this->throwIfInvalidNumber($end);

            $fluctuation = $origin->replaceVariables($this->fluctuation);
            $this->throwIfInvalidNumber($fluctuation, null, null, [0]);

            $this->initCounter($counterName, $start, $end, $fluctuation);
        }

        $counter = $this->counter;

        for ($i=$counter["current"]; $i<=$counter["end"]; $i+=$counter["fluctuation"]) {
            $this->counter["current"] += $counter["fluctuation"];
            $origin->addVariable(new NumberVariable($i, $counter["name"]));
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
        $this->execute($last[0], false);
    }

    public function initCounter(string $counter, int $start, int $end, int $fluctuation) {
        $this->counter = [
            "name" => $counter,
            "start" => $start,
            "current" => $start,
            "end" => $end,
            "fluctuation" => $fluctuation,
        ];
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
                        (new ActionContainerForm)->sendActionList($player, $parent);
                        break;
                    case 1:
                        (new ActionContainerForm)->sendActionList($player, $this);
                        break;
                    case 2:
                        $this->sendSettingCounter($player);
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

    public function sendSettingCounter(Player $player, array $default = [], array $errors = []) {
        (new CustomForm("@action.for.setting"))
            ->setContents([
                new ExampleInput("@action.for.counterName", "i", $default[0] ?? $this->getCounterName(), true),
                new ExampleNumberInput("@action.for.start", "0", $default[1] ?? $this->getStartIndex(), true),
                new ExampleNumberInput("@action.for.end", "9", $default[2] ?? $this->getEndIndex(), true, null, null, [0]),
                new Input("@action.for.fluctuation", Language::get("form.example", ["1"]), $default[3] ?? $this->getFluctuation())
            ])->onReceive(function (Player $player, array $data) {
                $this->setCounterName($data[0]);
                $this->setStartIndex($data[1]);
                $this->setEndIndex($data[2]);
                $this->setFluctuation($data[3]);
                $this->sendCustomMenu($player, ["@form.changed"]);
            })->addErrors($errors)->show($player);
    }

    public function loadSaveData(array $contents): Action {
        foreach ($contents[0] as $content) {
            $action = Action::loadSaveDataStatic($content);
            $this->addAction($action);
        }

        $this->setCounterName($contents[1]);
        $this->setStartIndex($contents[2]);
        $this->setEndIndex($contents[3]);
        $this->setFluctuation($contents[4]);
        return $this;
    }

    public function serializeContents(): array {
        return  [
            $this->actions,
            $this->counterName,
            $this->startIndex,
            $this->endIndex,
            $this->fluctuation,
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