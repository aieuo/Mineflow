<?php

namespace aieuo\mineflow\condition\script;

use pocketmine\entity\Entity;
use pocketmine\Player;
use aieuo\mineflow\utils\Session;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\ui\ConditionForm;
use aieuo\mineflow\ui\ConditionContainerForm;
use aieuo\mineflow\script\Script;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\condition\script\ConditionScript;
use aieuo\mineflow\condition\Conditionable;
use aieuo\mineflow\condition\ConditionContainer;
use aieuo\mineflow\condition\Condition;
use aieuo\mineflow\FormAPI\element\Button;

class AndScript extends ConditionScript implements ConditionContainer {

    protected $id = self::SCRIPT_AND;

    protected $name = "@script.and.name";
    protected $description = "@script.and.description";

    protected $category = Categories::CATEGORY_CONDITION_SCRIPT;

    /** @var Conditionable[] */
    protected $conditions = [];

    public function __construct(array $conditions = [], ?string $customName = null) {
        $this->conditions = $conditions;
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

    public function getDetail(): string {
        $details = ["----------and-----------"];
        foreach ($this->conditions as $condition) {
            $details[] = $condition->getDetail();
        }
        $details[] = "------------------------";
        return implode("\n", $details);
    }

    public function execute(?Entity $target, ?Recipe $origin = null): ?bool {
        $matched = true;
        foreach ($this->conditions as $condition) {
            $result = $condition->execute($target);
            if ($result === null) return null;
            if (!$result) $matched = false;
        }
        return $matched;
    }

    public function sendEditForm(Player $player, array $messages = []) {
        $detail = trim($this->getDetail());
        (new ListForm($this->getName()))
            ->setContent(empty($detail) ? "@form.recipe.recipeMenu.noActions" : $detail)
            ->addButtons([
                new Button("@form.script.if.editCondition"),
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
                        (new ConditionForm)->sendConfirmDelete($player, $this, $parent);
                        break;
                    case 2:
                        array_pop($parents);
                        $session->set("parents", $parents);
                        (new ConditionContainerForm)->sendConditionList($player, $parent);
                        break;
                }
            })->addMessages($messages)->show($player);
    }

    public function parseFromSaveData(array $contents): ?Script {
        foreach ($contents as $content) {
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
        return $this;
    }

    public function serializeContents(): array {
        return $this->conditions;
    }
}