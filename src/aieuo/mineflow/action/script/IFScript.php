<?php

namespace aieuo\mineflow\action\script;

use pocketmine\entity\Entity;
use pocketmine\Player;
use aieuo\mineflow\utils\Session;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\ui\ConditionContainerForm;
use aieuo\mineflow\ui\ActionForm;
use aieuo\mineflow\ui\ActionContainerForm;
use aieuo\mineflow\script\Script;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\condition\Conditionable;
use aieuo\mineflow\condition\ConditionContainer;
use aieuo\mineflow\condition\Condition;
use aieuo\mineflow\action\script\ActionScript;
use aieuo\mineflow\action\process\Process;
use aieuo\mineflow\action\ActionContainer;
use aieuo\mineflow\action\Action;
use aieuo\mineflow\FormAPI\element\Button;

class IFScript extends ActionScript implements ActionContainer, ConditionContainer {

    protected $id = self::SCRIPT_IF;

    protected $name = "@script.if.name";
    protected $description = "@script.if.description";

    protected $category = Categories::CATEGORY_ACTION_SCRIPT;

    /** @var Conditionable[] */
    private $conditions = [];
    /** @var Action[] */
    private $actions = [];

    public function __construct(array $conditions = [], array $actions = [], ?string $customName = null) {
        $this->conditions = $conditions;
        $this->actions = $actions;
        $this->setCustomName($customName);
    }

    public function addCondition(Conditionable $condition): void {
        $this->conditions[] = $condition;
    }

    public function getCondition(int $index): ?Conditionable {
        return $this->conditions[$index] ?? null;
    }

    public function getConditions(): array {
        return $this->conditions;
    }

    public function removeCondition(int $index): void {
        unset($this->conditions[$index]);
        $this->conditions = array_merge($this->conditions);
    }

    public function addAction(Action $action): void {
        $this->actions[] = $action;
    }

    public function getAction(int $index): ?Action {
        return $this->actions[$index] ?? null;
    }

    public function getActions(): array {
        return $this->actions;
    }

    public function removeAction(int $index): void {
        unset($this->actions[$index]);
        $this->actions = array_merge($this->actions);
    }

    public function getDetail(): string {
        $details = ["", "==============if================"];
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

    public function execute(?Entity $target, ?Recipe $origin = null): ?bool {
        $matched = true;
        foreach ($this->conditions as $condition) {
            $result = $condition->execute($target, $origin);

            if ($result === null) return null;
            if (!$result) $matched = false;
        }
        if (!$matched) return false;

        foreach ($this->actions as $action) {
            $action->execute($target, $origin);
        }
        return true;
    }

    public function sendEditForm(Player $player, array $messages = []) {
        $detail = trim($this->getDetail());
        (new ListForm($this->getName()))
            ->setContent(empty($detail) ? "@recipe.noActions" : $detail)
            ->addButtons([
                new Button("@condition.edit"),
                new Button("@action.edit"),
                new Button("@form.delete"),
                new Button("@form.back"),
            ])->onRecive(function (Player $player, ?int $data) {
                $session = Session::getSession($player);
                if ($data === null) {
                    $session->removeAll();
                    return;
                }
                $parents = $session->get("parents");
                $parent = end($parents);
                switch ($data) {
                    case 0:
                        (new ConditionContainerForm)->sendConditionList($player, $this);
                        break;
                    case 1:
                        (new ActionContainerForm)->sendActionList($player, $this);
                        break;
                    case 2:
                        (new ActionForm)->sendConfirmDelete($player, $this, $parent);
                        break;
                    case 3:
                        array_pop($parents);
                        $session->set("parents", $parents);
                        (new ActionContainerForm)->sendActionList($player, $parent);
                        break;
                }
            })->addMessages($messages)->show($player);
    }

    public function parseFromSaveData(array $contents): ?Script {
        if (!isset($contents[1])) return null;
        foreach ($contents[0] as $content) {
            switch ($content["type"]) {
                case Recipe::CONTENT_TYPE_CONDITION:
                    $condition = Condition::parseFromSaveDataStatic($content);
                    break;
                case Recipe::CONTENT_TYPE_SCRIPT:
                    $condition = Script::parseFromSaveDataStatic($content);
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
                    $action = Process::parseFromSaveDataStatic($content);
                    break;
                case Recipe::CONTENT_TYPE_SCRIPT:
                    $action = Script::parseFromSaveDataStatic($content);
                    break;
                default:
                    return null;
            }
            if ($action === null) return null;
            $this->addAction($action);
        }
        return $this;
    }

    public function serializeContents(): array {
        return  [
            $this->conditions,
            $this->actions
        ];
    }
}