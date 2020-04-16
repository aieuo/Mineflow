<?php

namespace aieuo\mineflow\flowItem\action;

use pocketmine\Player;
use aieuo\mineflow\utils\Session;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\ui\ActionForm;
use aieuo\mineflow\ui\ActionContainerForm;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\element\Button;

class ElseAction extends Action implements ActionContainer {
    use ActionContainerTrait;

    protected $id = self::ACTION_ELSE;

    protected $name = "action.else.name";
    protected $detail = "action.else.description";

    protected $category = Category::SCRIPT;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    /* @var bool */
    private $lastResult;

    public function __construct(array $actions = [], ?string $customName = null) {
        $this->setActions($actions);
        $this->setCustomName($customName);
    }

    public function getDetail(): string {
        $details = ["==============else=============="];
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
        $lastResult = $this->getParent()->getLastActionResult();
        if ($lastResult === null) throw new \UnexpectedValueException();
        if ($lastResult) return false;

        foreach ($this->actions as $action) {
            $this->lastResult = $action->parent($this)->execute($origin);
        }
        return true;
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
                new Button("@action.edit"),
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
                        (new ActionContainerForm)->sendActionList($player, $this);
                        break;
                    case 2:
                        (new ActionForm)->sendConfirmDelete($player, $this, $parent);
                        break;
                }
            })->onClose(function (Player $player) {
                Session::getSession($player)->removeAll();
            })->addMessages($messages)->show($player);
    }

    public function loadSaveData(array $contents): Action {
        foreach ($contents as $content) {
            if ($content["type"] !== Recipe::CONTENT_TYPE_ACTION) {
                throw new \InvalidArgumentException("invalid content type: \"{$content["type"]}\"");
            }

            $action = Action::loadSaveDataStatic($content);
            $this->addAction($action);
        }
        return $this;
    }

    public function serializeContents(): array {
        return $this->actions;
    }

    public function isDataValid(): bool {
        return true;
    }

    public function allowDirectCall(): bool {
        return false;
    }
}