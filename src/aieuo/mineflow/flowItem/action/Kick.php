<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\Main;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use pocketmine\scheduler\ClosureTask;

class Kick extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;

    protected $id = self::KICK;

    protected $name = "action.kick.name";
    protected $detail = "action.kick.detail";
    protected $detailDefaultReplace = ["player", "reason"];

    protected $category = Category::PLAYER;

    /** @var string */
    private $reason;
    /** @var bool */
    private $isAdmin;

    public function __construct(string $player = "", string $reason = "", bool $isAdmin = false) {
        $this->setPlayerVariableName($player);
        $this->reason = $reason;
        $this->isAdmin = $isAdmin;
    }

    public function setReason(string $reason): void {
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
    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $reason = $source->replaceVariables($this->getReason());

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function (int $currentTick) use ($player, $reason): void {
            $player->kick($reason, $this->isAdmin);
        }), 1);
        yield true;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
            new ExampleInput("@action.kick.form.reason", "aieuo", $this->getReason()),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setPlayerVariableName($content[0]);
        $this->setReason($content[1]);
        if (isset($content[2]) and is_bool($content[2])) $this->isAdmin = $content[2];
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->getReason(), $this->isAdmin];
    }
}