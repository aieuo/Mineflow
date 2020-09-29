<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemContainer;
use aieuo\mineflow\flowItem\FlowItemContainerTrait;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\ui\FlowItemContainerForm;
use aieuo\mineflow\ui\FlowItemForm;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Session;
use aieuo\mineflow\variable\NumberVariable;
use pocketmine\Player;

class ForAction extends FlowItem implements FlowItemContainer {
    use FlowItemContainerTrait;

    protected $id = self::ACTION_FOR;

    protected $name = "action.for.name";
    protected $detail = "action.for.description";

    protected $category = Category::SCRIPT;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    protected $permission = self::PERMISSION_LEVEL_1;

    /** @var string */
    private $counterName = "i";
    /** @var string */
    private $startIndex = "0";
    /** @var string */
    private $endIndex = "9";
    /** string */
    private $fluctuation = "1";

    /** @var array */
    private $counter;

    public function __construct(array $actions = [], ?string $customName = null) {
        $this->setItems($actions, FlowItemContainer::ACTION);
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
        $counterName = $origin->replaceVariables($this->counterName);

        $start = $origin->replaceVariables($this->startIndex);
        $this->throwIfInvalidNumber($start);

        $end = $origin->replaceVariables($this->endIndex);
        $this->throwIfInvalidNumber($end);

        $fluctuation = $origin->replaceVariables($this->fluctuation);
        $this->throwIfInvalidNumber($fluctuation, null, null, [0]);

        for ($i = $start; $i <= $end; $i += $fluctuation) {
            $origin->addVariable(new NumberVariable($i, $counterName));
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

    public function sendSettingCounter(Player $player) {
        (new CustomForm("@action.for.setting"))
            ->setContents([
                new ExampleInput("@action.for.counterName", "i", $this->getCounterName(), true),
                new ExampleNumberInput("@action.for.start", "0", $this->getStartIndex(), true),
                new ExampleNumberInput("@action.for.end", "9", $this->getEndIndex(), true),
                new ExampleNumberInput("@action.for.fluctuation", "1", $this->getFluctuation(), true, null, null, [0])
            ])->onReceive(function (Player $player, array $data) {
                $this->setCounterName($data[0]);
                $this->setStartIndex($data[1]);
                $this->setEndIndex($data[2]);
                $this->setFluctuation($data[3]);
                $this->sendCustomMenu($player, ["@form.changed"]);
            })->show($player);
    }

    public function loadSaveData(array $contents): FlowItem {
        foreach ($contents[0] as $content) {
            $action = FlowItem::loadSaveDataStatic($content);
            $this->addItem($action, FlowItemContainer::ACTION);
        }

        $this->setCounterName($contents[1]);
        $this->setStartIndex($contents[2]);
        $this->setEndIndex($contents[3]);
        $this->setFluctuation($contents[4]);
        return $this;
    }

    public function serializeContents(): array {
        return [
            $this->getItems(FlowItemContainer::ACTION),
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
        foreach ($this->getItems(FlowItemContainer::ACTION) as $k => $action) {
            $actions[$k] = clone $action;
        }
        $this->setItems($actions, FlowItemContainer::ACTION);
    }
}