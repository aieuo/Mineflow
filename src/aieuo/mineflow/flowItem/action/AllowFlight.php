<?php
declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;

class AllowFlight extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;

    protected $id = self::ALLOW_FLIGHT;

    protected $name = "action.allowFlight.name";
    protected $detail = "action.allowFlight.detail";
    protected $detailDefaultReplace = ["player", "allow"];

    protected $category = Category::PLAYER;

    protected $permission = self::PERMISSION_LEVEL_1;

    /** @var bool */
    private $allow;

    public function __construct(string $player = "", string $allow = "true") {
        $this->setPlayerVariableName($player);
        $this->allow = $allow === "true";
    }

    public function setAllow(bool $allow): void {
        $this->allow = $allow;
    }

    public function isAllow(): bool {
        return $this->allow;
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPlayerVariableName(), ["action.allowFlight.".($this->isAllow() ? "allow" : "notAllow")]]);
    }

    public function execute(Recipe $origin): \Generator {
        $this->throwIfCannotExecute();

        $player = $this->getPlayer($origin);
        $this->throwIfInvalidPlayer($player);

        $player->setAllowFlight($this->isAllow());
        yield true;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
            new Toggle("@action.allowFlight.form.allow", $this->isAllow()),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setPlayerVariableName($content[0]);
        $this->setAllow($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->isAllow()];
    }
}