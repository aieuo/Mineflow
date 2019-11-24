<?php

namespace aieuo\mineflow\action\script;

use pocketmine\entity\Entity;
use pocketmine\Player;
use aieuo\mineflow\utils\Session;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\ui\ActionForm;
use aieuo\mineflow\ui\ActionContainerForm;
use aieuo\mineflow\script\Script;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\action\script\ActionScript;
use aieuo\mineflow\action\process\Process;
use aieuo\mineflow\action\ActionContainer;
use aieuo\mineflow\action\Action;
use aieuo\mineflow\FormAPI\element\Button;

class ElseScript extends ActionScript implements ActionContainer {

    protected $id = self::SCRIPT_ELSE;

    protected $name = "@script.else.name";
    protected $description = "@script.else.description";

    protected $category = Categories::CATEGORY_ACTION_SCRIPT;

    /** @var Action[] */
    private $actions = [];

    public function __construct(array $actions = [], ?string $customName = null) {
        $this->actions = $actions;
        $this->setCustomName($customName);
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
        $details = ["==============else=============="];
        foreach ($this->actions as $action) {
            $details[] = $action->getDetail();
        }
        $details[] = "================================";
        return implode("\n", $details);
    }

    public function execute(?Entity $target, ?Recipe $origin = null): ?bool {
        if (!($origin instanceof Recipe)) return null;

        $lastResult = $origin->getLastActionResult();
        if ($lastResult === null) return null;
        if ($lastResult) return false;

        foreach ($this->actions as $action) {
            $result = $action->execute($target, $origin);
            if ($result === null) return null;
        }
        return true;
    }

    public function sendEditForm(Player $player, array $messages = []) {
        $detail = trim($this->getDetail());
        (new ListForm($this->getName()))
            ->setContent(empty($detail) ? "@recipe.noActions" : $detail)
            ->addButtons([
                new Button("@form.back"),
                new Button("@action.edit"),
                new Button("@form.delete"),
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
            })->addMessages($messages)->show($player);
    }

    public function parseFromSaveData(array $contents): ?Script {
        foreach ($contents as $content) {
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
        return $this->actions;
    }
}