<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\mineflow\CancelToggle;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\Main;
use aieuo\mineflow\recipe\Recipe;
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

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;

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
    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $reason = $origin->replaceVariables($this->getReason());

        $player = $this->getPlayer($origin);
        $this->throwIfInvalidPlayer($player);

        Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function (int $currentTick) use ($player, $reason): void {
            $player->kick($reason, $this->isAdmin);
        }), 1);
        yield true;
    }

    public function getEditForm(array $variables = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
                new ExampleInput("@action.kick.form.reason", "aieuo", $this->getReason()),
                new CancelToggle()
            ]);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], $data[2]], "cancel" => $data[3]];
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