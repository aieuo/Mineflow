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
use aieuo\mineflow\ui\ScriptForm;
use aieuo\mineflow\variable\NumberVariable;

class RepeatScript extends ActionScript implements ActionContainer {

    protected $id = self::SCRIPT_REPEAT;

    protected $name = "@script.repeat.name";
    protected $description = "@script.repeat.description";

    protected $category = Categories::CATEGORY_ACTION_SCRIPT;

    /** @var Action[] */
    private $actions = [];

    /** @var int */
    private $repeatCount = 1;

    public function __construct(array $actions = [], int $count = 1, ?string $customName = null) {
        $this->actions = $actions;
        $this->repeatCount = $count;
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

    public function setRepeatCount(int $count): void {
        $this->repeatCount = $count;
    }

    public function getRepeatCount(): int {
        return $this->repeatCount;
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

    public function execute(?Entity $target, ?Recipe $origin = null): ?bool {
        for ($i=0; $i<$this->repeatCount; $i++) {
            if ($origin instanceof Recipe) $origin->addVariable(new NumberVariable("i", $i));
            foreach ($this->actions as $action) {
                $result = $action->execute($target, $origin);
                if ($result === null) return null;
            }
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
                new Button("@script.repeat.editCount"),
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
                        (new ScriptForm)->sendSetRepeatCount($player, $this);
                        break;
                    case 3:
                        (new ActionForm)->sendConfirmDelete($player, $this, $parent);
                        break;
                }
            })->addMessages($messages)->show($player);
    }

    public function parseFromSaveData(array $contents): ?Script {
        if (!isset($contents[1])) return null;
        $this->setRepeatCount($contents[0]);

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
            $this->repeatCount,
            $this->actions
        ];
    }
}