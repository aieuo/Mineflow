<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player\bossbar;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\utils\Bossbar;
use pocketmine\network\mcpe\protocol\types\BossBarColor;
use SOFe\AwaitGenerator\Await;
use function array_keys;
use function array_search;

class ShowBossbar extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;
    use ActionNameWithMineflowLanguage;

    private array $colors = [
        "pink" => BossBarColor::PINK,
        "blue" => BossBarColor::BLUE,
        "red" => BossBarColor::RED,
        "green" => BossBarColor::GREEN,
        "yellow" => BossBarColor::YELLOW,
        "purple" => BossBarColor::PURPLE,
        "white" => BossBarColor::WHITE,
    ];

    public function __construct(
        string         $player = "",
        private string $title = "",
        private string $max = "",
        private string $value = "",
        private string $color = "purple",
        private string $barId = ""
    ) {
        parent::__construct(self::SHOW_BOSSBAR, FlowItemCategory::BOSSBAR);

        $this->setPlayerVariableName($player);
    }

    public function getDetailDefaultReplaces(): array {
        return ["player", "title", "max", "value", "color", "id"];
    }

    public function getDetailReplaces(): array {
        return [$this->getPlayerVariableName(), $this->getTitle(), $this->getMax(), $this->getValue(), $this->getColor(), $this->getBarId()];
    }

    public function setTitle(string $health): void {
        $this->title = $health;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function setMax(string $max): void {
        $this->max = $max;
    }

    public function getMax(): string {
        return $this->max;
    }

    public function setValue(string $value): void {
        $this->value = $value;
    }

    public function getValue(): string {
        return $this->value;
    }

    public function setColor(string $color): void {
        $this->color = $color;
    }

    public function getColor(): string {
        return $this->color;
    }

    public function setBarId(string $barId): void {
        $this->barId = $barId;
    }

    public function getBarId(): string {
        return $this->barId;
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "" and $this->title !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $title = $source->replaceVariables($this->getTitle());
        $max = $this->getFloat($source->replaceVariables($this->getMax()), 1);
        $value = $this->getFloat($source->replaceVariables($this->getValue()));
        $id = $source->replaceVariables($this->getBarId());
        $color = $this->colors[$source->replaceVariables($this->getColor())] ?? BossBarColor::PURPLE;

        $player = $this->getOnlinePlayer($source);

        Bossbar::add($player, $id, $title, $max, $value / $max, $color);

        yield Await::ALL;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
            new ExampleInput("@action.showBossbar.form.title", "20", $this->getTitle(), true),
            new ExampleNumberInput("@action.showBossbar.form.max", "20", $this->getMax(), true),
            new ExampleNumberInput("@action.showBossbar.form.value", "20", $this->getValue(), true),
            new ExampleInput("@action.showBossbar.form.id", "20", $this->getBarId(), true),
            new Dropdown("@action.showBossbar.form.color", array_keys($this->colors), (int)array_search($this->getColor(), array_keys($this->colors), true))
        ];
    }

    public function parseFromFormData(array $data): array {
        $data[5] = array_keys($this->colors)[$data[5]] ?? "purple";
        return $data;
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setPlayerVariableName($content[0]);
        $this->setTitle($content[1]);
        $this->setMax($content[2]);
        $this->setValue($content[3]);
        $this->setBarId($content[4]);
        $this->setColor($content[5] ?? "purple");
        return $this;
    }

    public function serializeContents(): array {
        return [
            $this->getPlayerVariableName(),
            $this->getTitle(),
            $this->getMax(),
            $this->getValue(),
            $this->getBarId(),
            $this->getColor(),
        ];
    }
}
