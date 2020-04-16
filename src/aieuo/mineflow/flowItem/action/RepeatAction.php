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
use aieuo\mineflow\ui\ScriptForm;
use aieuo\mineflow\variable\NumberVariable;

class RepeatAction extends Action implements ActionContainer {
    use ActionContainerTrait;

    protected $id = self::ACTION_REPEAT;

    protected $name = "action.repeat.name";
    protected $detail = "action.repeat.description";

    protected $category = Category::SCRIPT;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    protected $permission = self::PERMISSION_LEVEL_1;

    /** @var string */
    private $repeatCount = "1";

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

    public function execute(Recipe $origin): bool {
        $count = $origin->replaceVariables($this->repeatCount);
        $this->throwIfInvalidNumber($count, 1);

        for ($i=0; $i<(int)$count; $i++) {
            $origin->addVariable(new NumberVariable($i, "i"));
            foreach ($this->actions as $action) {
                $action->execute($origin);
            }
        }
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
                new Button("@action.edit"),
                new Button("@action.repeat.editCount"),
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
                        (new ScriptForm)->sendSetRepeatCount($player, $this);
                        break;
                    case 3:
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
            if ($content["type"] !== Recipe::CONTENT_TYPE_ACTION) {
                throw new \InvalidArgumentException("invalid content type: \"{$content["type"]}\"");
            }

            $action = Action::loadSaveDataStatic($content);
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

    public function isDataValid(): bool {
        return true;
    }

    public function allowDirectCall(): bool {
        return false;
    }
}