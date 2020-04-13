<?php

namespace aieuo\mineflow\flowItem\condition;

use pocketmine\Player;
use aieuo\mineflow\utils\Session;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\ui\ConditionForm;
use aieuo\mineflow\ui\ConditionContainerForm;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\element\Button;

class AndScript extends Condition implements ConditionContainer {
    use ConditionContainerTrait;

    protected $id = self::CONDITION_AND;

    protected $name = "condition.and.name";
    protected $detail = "condition.and.detail";

    protected $category = Category::SCRIPT;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    public function getDetail(): string {
        $details = ["----------and-----------"];
        foreach ($this->conditions as $condition) {
            $details[] = $condition->getDetail();
        }
        $details[] = "------------------------";
        return implode("\n", $details);
    }

    public function getContainerName(): string {
        return empty($this->getCustomName()) ? $this->getName() : $this->getCustomName();
    }

    public function execute(Recipe $origin): bool {
        $matched = true;
        foreach ($this->conditions as $condition) {
            $result = $condition->execute($origin);
            if (!$result) $matched = false;
        }
        return $matched;
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
                new Button("@form.delete"),
            ])->onReceive(function (Player $player, ?int $data) {
                $session = Session::getSession($player);
                $parents = $session->get("parents");
                $parent = end($parents);
                switch ($data) {
                    case 0:
                        array_pop($parents);
                        $session->set("parents", $parents);
                        (new ConditionContainerForm)->sendConditionList($player, $parent);
                        break;
                    case 1:
                        (new ConditionContainerForm)->sendConditionList($player, $this);
                        break;
                    case 2:
                        (new ConditionForm)->sendConfirmDelete($player, $this, $parent);
                        break;
                }
            })->onClose(function (Player $player) {
                Session::getSession($player)->removeAll();
            })->addMessages($messages)->show($player);
    }

    public function loadSaveData(array $contents): Condition {
        foreach ($contents as $content) {
            if ($content["type"] !== Recipe::CONTENT_TYPE_CONDITION) {
                throw new \InvalidArgumentException("invalid content type: \"{$content["type"]}\"");
            }

            $condition = Condition::loadSaveDataStatic($content);
            $this->addCondition($condition);
        }
        return $this;
    }

    public function serializeContents(): array {
        return $this->conditions;
    }

    public function isDataValid(): bool {
        return true;
    }
}