<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\ExampleInput;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\Main;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\CustomForm;
use pocketmine\scheduler\ClosureTask;

class Kick extends Action implements PlayerFlowItem {
    use PlayerFlowItemTrait;

    protected $id = self::KICK;

    protected $name = "action.kick.name";
    protected $detail = "action.kick.detail";
    protected $detailDefaultReplace = ["player", "reason"];

    protected $category = Category::PLAYER;

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;

    /** @var string */
    private $reason;
    /** @var bool */
    private $isAdmin;

    public function __construct(string $player = "target", string $reason = "", bool $isAdmin = false) {
        $this->setPlayerVariableName($player);
        $this->reason = $reason;
        $this->isAdmin = $isAdmin;
    }

    public function setReason(string $reason) {
        $this->reason = $reason;
    }

    public function getReason(): string {
        return $this->reason;
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "" and $this->reason !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPlayerVariableName(), $this->getReason()]);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $reason = $origin->replaceVariables($this->getReason());

        $player = $this->getPlayer($origin);
        $this->throwIfInvalidPlayer($player);

        Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function (int $currentTick) use ($player, $reason): void {
            $player->kick($reason, $this->isAdmin);
        }), 1);
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@flowItem.form.target.player", "target", $default[1] ?? $this->getPlayerVariableName()),
                new ExampleInput("@action.kick.form.reason", "aieuo", $default[2] ?? $this->getReason()),
                new CancelToggle()
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[1] === "") $data[1] = "target";
        return ["contents" => [$data[1], $data[2]], "cancel" => $data[3], "errors" => $errors];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[1])) throw new \OutOfBoundsException();
        $this->setPlayerVariableName($content[0]);
        $this->setReason($content[1]);
        if (isset($content[2]) and is_bool($content[2])) $this->isAdmin = $content[2];
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->getReason(), $this->isAdmin];
    }
}