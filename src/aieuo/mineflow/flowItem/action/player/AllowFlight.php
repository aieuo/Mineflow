<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;

class AllowFlight extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;

    protected string $id = self::ALLOW_FLIGHT;

    protected string $name = "action.allowFlight.name";
    protected string $detail = "action.allowFlight.detail";
    protected array $detailDefaultReplace = ["player", "allow"];

    protected string $category = Category::PLAYER;

    protected int $permission = self::PERMISSION_LEVEL_1;

    private bool $allow;

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
        return Language::get($this->detail, [$this->getPlayerVariableName(), ["action.allowFlight.".($this->isAllow() ? "allow" : "notAllow")]]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $player = $this->getOnlinePlayer($source);

        $player->setAllowFlight($this->isAllow());
        yield FlowItemExecutor::CONTINUE;
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