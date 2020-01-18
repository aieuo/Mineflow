<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\condition\ConditionContainer;
use aieuo\mineflow\flowItem\condition\ConditionContainerTrait;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Logger;
use pocketmine\entity\Entity;
use pocketmine\Player;
use aieuo\mineflow\utils\Session;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\ui\ConditionContainerForm;
use aieuo\mineflow\ui\ActionForm;
use aieuo\mineflow\ui\ActionContainerForm;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\element\Button;

class IFAction extends Action implements ActionContainer, ConditionContainer {
    use ActionContainerTrait, ConditionContainerTrait;

    protected $id = self::ACTION_IF;

    protected $name = "@action.if.name";
    protected $detail = "@action.if.description";

    protected $category = Categories::CATEGORY_ACTION_SCRIPT;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    public function __construct(array $conditions = [], array $actions = [], ?string $customName = null) {
        $this->setConditions($conditions);
        $this->setActions($actions);
        $this->setCustomName($customName);
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

    public function execute(?Entity $target, Recipe $origin): ?bool {
        $matched = true;
        foreach ($this->conditions as $condition) {
            $result = $condition->execute($target, $origin);

            if ($result === null) return null;
            if (!$result) $matched = false;
        }
        if (!$matched) return false;

        foreach ($this->actions as $action) {
            $result = $action->execute($target, $origin);
            if ($result === null) return null;
        }
        return true;
    }
    public function isDataValid(): bool {
        return true;
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
                        (new ActionForm)->sendConfirmDelete($player, $this, $parent);
                        break;
                }
            })->addMessages($messages)->show($player);
    }

    public function loadSaveData(array $contents): ?Action {
        if (!isset($contents[1])) return null;
        foreach ($contents[0] as $i => $content) {
            switch ($content["type"]) {
                case Recipe::CONTENT_TYPE_CONDITION:
                    $condition = Condition::loadSaveDataStatic($content);
                    break;
                default:
                    return null;
            }
            if ($condition === null) {
                Logger::warning(Language::get("recipe.load.failed.condition", [$i, $content["id"] ?? "id?"]));
                return null;
            }
            $this->addCondition($condition);
        }

        foreach ($contents[1] as $i => $content) {
            switch ($content["type"]) {
                case Recipe::CONTENT_TYPE_PROCESS:
                    $action = Action::loadSaveDataStatic($content);
                    break;
                default:
                    return null;
            }
            if ($action === null) {
                Logger::warning(Language::get("recipe.load.failed.action", [$i, $content["id"] ?? "id?"]));
                return null;
            }
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